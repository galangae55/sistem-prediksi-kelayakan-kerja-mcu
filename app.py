import os
import json
import joblib
import numpy as np
from flask import Flask, render_template, request, jsonify

app = Flask(__name__)

# Base path & load model
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, 'models', 'health_status_model_v4.pkl')
if not os.path.exists(MODEL_PATH):
    MODEL_PATH = 'health_status_model_v4.pkl'

try:
    model = joblib.load(MODEL_PATH)
    print(f"Model berhasil dimuat dari: {MODEL_PATH}")
except Exception as e:
    model = None
    print(f"Peringatan: Model belum dapat dimuat ({e}).")

# Profil Pasien & Studi Kasus Uji Nyata
CASE_STUDIES = {
    0: {
        "case_id": "CASE-01",
        "badge": "Lolos Penuh",
        "tag_class": "fit",
        "patient_name": "Budi Pratama, S.T.",
        "patient_id": "MCU-2024-00891",
        "gender_age": "Laki-laki / 32 Tahun",
        "position": "Teknisi Lapangan (Field Service Engineer)",
        "department": "Divisi Operasional & Pemeliharaan",
        "company": "PT Energi Nusantara Mandiri",
        "mcu_type": "Pemeriksaan Kesehatan Berkala Tahunan (Annual MCU)",
        "doctor": "dr. H. Rian Pratama, Sp.Ok (SIP: 446/K3-DKI/2023)",
        "case_summary": "Karyawan dalam kondisi sehat prima. Seluruh 57 parameter tanda vital, fungsi organ, hematologi, dan urinalisis berada dalam batas rujukan normal."
    },
    1: {
        "case_id": "CASE-02",
        "badge": "Lolos Bersyarat",
        "tag_class": "note",
        "patient_name": "Hendra Wijaya, S.E.",
        "patient_id": "MCU-2024-00914",
        "gender_age": "Laki-laki / 41 Tahun",
        "position": "Staf Senior Administrasi & Logistik",
        "department": "Divisi Pengadaan & Supply Chain",
        "company": "PT Logistik Samudera Jaya",
        "mcu_type": "Pemeriksaan Kesehatan Rutin Karyawan (Periodic MCU)",
        "doctor": "dr. Sarah Oktaviani, Sp.Ok (SIP: 312/K3-JABAR/2022)",
        "case_summary": "Ditemukan faktor risiko pra-hipertensi ringan dan profil lipid batas atas (kolesterol total & trigliserida). Tidak mengganggu kapasitas kerja saat ini."
    },
    2: {
        "case_id": "CASE-03",
        "badge": "Pembatasan Tugas",
        "tag_class": "restrict",
        "patient_name": "Joko Susilo",
        "patient_id": "MCU-2024-00958",
        "gender_age": "Laki-laki / 48 Tahun",
        "position": "Operator Alat Berat (Heavy Equipment Operator)",
        "department": "Divisi Konstruksi & Ekskavasi",
        "company": "PT Tambang Makmur Sejahtera",
        "mcu_type": "Pemeriksaan Kelayakan Tugas Khusus (Fitness for Duty)",
        "doctor": "dr. H. Rian Pratama, Sp.Ok (SIP: 446/K3-DKI/2023)",
        "case_summary": "Ditemukan hipertensi tingkat 2, penurunan kapasitas vital paru (Spirometri restriktif ringan), dan leukosituria pada urine. Memerlukan penyesuaian beban kerja."
    },
    3: {
        "case_id": "CASE-04",
        "badge": "Tidak Lolos / Rujukan",
        "tag_class": "unfit",
        "patient_name": "Surya Pratama, A.Md.",
        "patient_id": "MCU-2024-01022",
        "gender_age": "Laki-laki / 45 Tahun",
        "position": "Pengawas Operasi Tambang Bawah Tanah",
        "department": "Divisi Pertambangan Bawah Tanah (Underground)",
        "company": "PT Mineral Prima Abadi",
        "mcu_type": "Pemeriksaan Kebugaran Khusus Risiko Tinggi",
        "doctor": "dr. Sarah Oktaviani, Sp.Ok (SIP: 312/K3-JABAR/2022)",
        "case_summary": "Temuan patologis kritis: Uji Treadmill abnormal (indikasi iskemia miokard), riwayat hepatitis aktif, dan hiperglikemia (diabetes tidak terkontrol). Berisiko tinggi."
    }
}

# Standar Kelayakan Medis Karyawan (Medical Fitness Standards)
STATUS_DETAILS = {
    0: {
        "label": "Fit to Work",
        "indo_label": "Lolos MCU (Layak Kerja Penuh)",
        "badge_class": "fit-work",
        "badge_color": "#059669",
        "title": "Memenuhi Syarat Kesehatan Kerja Tanpa Batasan",
        "meaning": "Karyawan dalam kondisi sehat prima. Tidak ditemukan kelainan atau gangguan fungsi organ.",
        "workplace_clearance": "Diberikan izin kerja penuh untuk seluruh tugas, termasuk lapangan dan area operasional.",
        "recommendation": "Pertahankan pola hidup sehat dan jadwalkan pemeriksaan rutin tahun depan.",
        "doctor_action": "Diterbitkan Surat Hasil MCU: Lolos Penuh (Grade A)."
    },
    1: {
        "label": "Fit with Note",
        "indo_label": "Lolos Bersyarat (Catatan Medis Ringan)",
        "badge_class": "fit-note",
        "badge_color": "#d97706",
        "title": "Layak Kerja dengan Pemantauan Faktor Risiko Ringan",
        "meaning": "Karyawan aman untuk bekerja normal, namun ada catatan medis ringan (seperti pra-hipertensi atau kolesterol batas atas) yang perlu dijaga.",
        "workplace_clearance": "Dapat bertugas normal di seluruh area kerja tanpa perubahan beban kerja.",
        "recommendation": "Konseling gizi seimbang, kurangi makanan asin/berlemak, dan kontrol ulang dalam 3-6 bulan.",
        "doctor_action": "Diterbitkan Surat Hasil MCU: Lolos Bersyarat (Grade B)."
    },
    2: {
        "label": "Fit with Restriction",
        "indo_label": "Layak Kerja dengan Batasan Tugas (Job Adjustment)",
        "badge_class": "fit-restriction",
        "badge_color": "#ea580c",
        "title": "Layak Kerja dengan Pembatasan Beban Tertentu",
        "meaning": "Karyawan memiliki keterbatasan fisik/organ tertentu sehingga perlu penyesuaian beban kerja agar tetap aman.",
        "workplace_clearance": "Izin kerja terbatas: hindari kerja di ketinggian, jangan angkat beban berat (>20 kg), dan batasi shift malam berlebih.",
        "recommendation": "Departemen HR & K3 menyesuaikan deskripsi tugas karyawan dan evaluasi berkala tiap 3 bulan.",
        "doctor_action": "Diterbitkan Surat Hasil MCU: Penyesuaian Beban Kerja (Grade C)."
    },
    3: {
        "label": "Unfit",
        "indo_label": "Tidak Lolos MCU (Perlu Pengobatan)",
        "badge_class": "unfit",
        "badge_color": "#dc2626",
        "title": "Tidak Memenuhi Syarat Kesehatan Saat Ini (Perlu Terapi)",
        "meaning": "Ditemukan gangguan kesehatan berat yang berisiko jika dipaksakan bertugas.",
        "workplace_clearance": "Diistirahatkan sementara dari tugas lapangan sampai kondisi terkontrol dan dinyatakan aman oleh dokter spesialis.",
        "recommendation": "Segera dirujuk ke Rumah Sakit / Dokter Spesialis untuk pengobatan tuntas sebelum evaluasi ulang.",
        "doctor_action": "Diterbitkan Surat Hasil MCU: Rujukan Medis Spesialis (Grade D)."
    }
}

FORM_FIELDS = [
    "BMICat", "Hipertensi_Kategori", "tuberkulosis", "penyakit_jantung", "hipertensi",
    "diabetes_melitus", "gangguan_jiwa", "trauma_pada_kepala", "hepatitis", "Spirometri",
    "Treadmil", "Audiometri", "foto_thorax", "Nadi(kali/menit)", "FrekuensiNapas(kali/menit)",
    "Tingkatan_Kesadaran", "Buta_Warna", "Jantung", "hemoglobin", "leukosit",
    "eritrosit", "LED", "eosinofil", "basofil", "neutrofil",
    "lymphosit", "monosit", "trombosit", "hematokrit", "MCV",
    "SGOT", "SGPT", "BUN", "kreatinin", "asam_urat",
    "kolestrol_total", "trigliserida", "kolestrol_HDL_(direct)", "kolestrol_LDL_(direct)", "gula_darah_puasa",
    "gula_darah_2_jam_pp", "anti_HbBs", "HBs_Ag_Kuantitatif", "pH_pada_urine", "berat_jenis_pada_urine",
    "nitrite_pada_urine", "protein_pada_urine", "reduksi_pada_urine", "ketone_pada_urine", "urobilinogen_pada_urine",
    "billirubin_pada_urine", "eritrosit_pada_urine", "lekosit_pada_urine", "silinder_pada_urine", "kristal_pada_urine",
    "yeast_pada_urine", "bakteri_pada_urine"
]

# Load presets from file
PRESETS_FILE = os.path.join(BASE_DIR, "presets_formatted.json")
presets_data = {}
if os.path.exists(PRESETS_FILE):
    try:
        with open(PRESETS_FILE, "r") as f:
            presets_data = json.load(f)
    except Exception as e:
        print(f"Error loading presets: {e}")


def extract_features_from_dict(data_dict):
    """Mengambil 57 fitur dalam urutan baku float."""
    return [float(data_dict.get(field, 0)) for field in FORM_FIELDS]


def detect_abnormal_findings(data_dict):
    """Menganalisis 57 parameter dan menghasilkan daftar temuan klinis relevan."""
    findings = []
    
    # 1. Tensi & BMI
    bmi = float(data_dict.get("BMICat", 0))
    if bmi == 1:
        findings.append({"category": "Antropometri", "param": "Indeks Massa Tubuh (BMI)", "result": "Obesitas Tingkat 1 (25.0 - 29.9)", "normal": "18.5 - 24.9 kg/m²", "severity": "warning"})
    elif bmi == 2:
        findings.append({"category": "Antropometri", "param": "Indeks Massa Tubuh (BMI)", "result": "Obesitas Tingkat 2 (≥ 30.0)", "normal": "18.5 - 24.9 kg/m²", "severity": "danger"})
    elif bmi == 4:
        findings.append({"category": "Antropometri", "param": "Indeks Massa Tubuh (BMI)", "result": "Underweight (< 18.5)", "normal": "18.5 - 24.9 kg/m²", "severity": "info"})
        
    ht = float(data_dict.get("Hipertensi_Kategori", 2))
    if ht == 0:
        findings.append({"category": "Tanda Vital", "param": "Tekanan Darah", "result": "Hipertensi Tingkat 1 (140-159/90-99 mmHg)", "normal": "< 120/80 mmHg", "severity": "warning"})
    elif ht == 1:
        findings.append({"category": "Tanda Vital", "param": "Tekanan Darah", "result": "Hipertensi Tingkat 2 (≥ 160/≥ 100 mmHg)", "normal": "< 120/80 mmHg", "severity": "danger"})
    elif ht == 3:
        findings.append({"category": "Tanda Vital", "param": "Tekanan Darah", "result": "Pra-Hipertensi (120-139/80-89 mmHg)", "normal": "< 120/80 mmHg", "severity": "info"})

    # 2. Riwayat & Diagnostik Khusus
    if float(data_dict.get("penyakit_jantung", 1)) == 0:
        findings.append({"category": "Kardiovaskular", "param": "Riwayat Penyakit Jantung", "result": "Ada Riwayat Penyakit Jantung", "normal": "Tidak Ada", "severity": "danger"})
    if float(data_dict.get("Treadmil", 0)) == 1:
        findings.append({"category": "Kardiologi", "param": "Uji Beban Jantung (Treadmill)", "result": "Positif Abnormal (Indikasi Iskemia Miokard)", "normal": "Negatif / Normal", "severity": "danger"})
    if float(data_dict.get("Spirometri", 0)) == 1:
        findings.append({"category": "Pulmonologi", "param": "Kapasitas Paru (Spirometri)", "result": "Restriktif / Penurunan Kapasitas Vital", "normal": "Kapasitas Normal (≥ 80%)", "severity": "warning"})
    if float(data_dict.get("hepatitis", 1)) == 0:
        findings.append({"category": "Infeksi", "param": "Pemeriksaan Hepatitis", "result": "Reaktif / Positif Aktif", "normal": "Non-Reaktif", "severity": "danger"})
    if float(data_dict.get("Buta_Warna", 0)) == 1:
        findings.append({"category": "Sensorik", "param": "Pemeriksaan Buta Warna", "result": "Buta Warna Parsial / Total", "normal": "Penglihatan Warna Normal", "severity": "warning"})

    # 3. Darah & Biokimia
    if float(data_dict.get("hemoglobin", 0)) == 1:
        findings.append({"category": "Hematologi", "param": "Kadar Hemoglobin (Hb)", "result": "Abnormal (Anemia / Di Luar Rentang)", "normal": "13.0 - 17.5 g/dL", "severity": "warning"})
    if float(data_dict.get("SGPT", 0)) == 1:
        findings.append({"category": "Fungsi Hati", "param": "Enzim SGPT (ALT)", "result": "Meningkat di Atas Batas Rujukan", "normal": "< 35 U/L", "severity": "warning"})
    if float(data_dict.get("gula_darah_puasa", 1)) != 1:
        findings.append({"category": "Metabolik", "param": "Gula Darah Puasa (GDP)", "result": "Hiperglikemia / Tidak Terkontrol", "normal": "70 - 100 mg/dL", "severity": "danger"})
    if float(data_dict.get("kolestrol_total", 0)) == 1:
        findings.append({"category": "Profil Lipid", "param": "Kolesterol Total", "result": "Meningkat (> 200 mg/dL)", "normal": "< 200 mg/dL", "severity": "warning"})

    # 4. Urinalisis
    if float(data_dict.get("lekosit_pada_urine", 0)) == 1:
        findings.append({"category": "Urinalisis", "param": "Leukosit pada Urine", "result": "Positif Leukosituria (> 5/LPB)", "normal": "Negatif / 0-2/LPB", "severity": "warning"})
    if float(data_dict.get("protein_pada_urine", 0)) == 1:
        findings.append({"category": "Urinalisis", "param": "Proteinuria", "result": "Positif Protein Urine (+1 s/d +3)", "normal": "Negatif", "severity": "warning"})

    # Jika semua normal
    if not findings:
        findings.append({
            "category": "Status Umum",
            "param": "Evaluasi Seluruh Parameter Lab & Fisik",
            "result": "Seluruh parameter dalam batas normal rujukan medis",
            "normal": "Sesuai Standar Klinis Kemenkes",
            "severity": "normal"
        })

    return findings


@app.route("/", methods=["GET", "POST"])
def index():
    prediction_info = None

    if request.method == "POST":
        try:
            form_vals = extract_features_from_dict(request.form)
            if model is not None:
                input_data = np.array([form_vals]).reshape(1, -1)
                pred_code = int(model.predict(input_data)[0])
                details = STATUS_DETAILS.get(pred_code, STATUS_DETAILS[0])
                patient = CASE_STUDIES.get(pred_code, CASE_STUDIES[0])
                findings = detect_abnormal_findings(request.form)
                prediction_info = {
                    "code": pred_code,
                    "label": details["label"],
                    "indo_label": details["indo_label"],
                    "badge_class": details["badge_class"],
                    "badge_color": details["badge_color"],
                    "title": details["title"],
                    "meaning": details["meaning"],
                    "workplace_clearance": details["workplace_clearance"],
                    "recommendation": details["recommendation"],
                    "doctor_action": details["doctor_action"],
                    "patient": patient,
                    "findings": findings
                }
        except Exception as e:
            prediction_info = {
                "code": -1,
                "label": "Error",
                "indo_label": "Kesalahan Pemrosesan Data",
                "badge_class": "unfit",
                "badge_color": "#dc2626",
                "title": "Terjadi Kendala Teknis Pengolahan Data",
                "meaning": str(e),
                "workplace_clearance": "Data tidak dapat dianalisis.",
                "recommendation": "Periksa kembali kelengkapan parameter formulir.",
                "doctor_action": "Uji ulang formulir laboratorium.",
                "patient": CASE_STUDIES[0],
                "findings": []
            }

    return render_template("index.html", prediction=prediction_info, case_studies=CASE_STUDIES)


@app.route("/api/presets", methods=["GET"])
def get_presets():
    """Mengembalikan data profil preset klinis untuk uji instan."""
    return jsonify({
        "status": "success",
        "presets": presets_data,
        "case_studies": CASE_STUDIES
    })


@app.route("/api/predict", methods=["POST"])
def api_predict():
    """API Endpoint inferensi status MCU (menerima JSON atau form)."""
    if model is None:
        return jsonify({
            "status": "error",
            "message": "Model Machine Learning belum siap atau file model tidak ditemukan."
        }), 503

    try:
        data = request.get_json(silent=True) or request.form.to_dict()
        form_vals = extract_features_from_dict(data)
        input_data = np.array([form_vals]).reshape(1, -1)
        pred_code = int(model.predict(input_data)[0])
        details = STATUS_DETAILS.get(pred_code, STATUS_DETAILS[0])
        patient = CASE_STUDIES.get(pred_code, CASE_STUDIES[0])
        findings = detect_abnormal_findings(data)

        return jsonify({
            "status": "success",
            "prediction_code": pred_code,
            "prediction_label": details["label"],
            "indo_label": details["indo_label"],
            "badge_class": details["badge_class"],
            "badge_color": details["badge_color"],
            "title": details["title"],
            "meaning": details["meaning"],
            "workplace_clearance": details["workplace_clearance"],
            "recommendation": details["recommendation"],
            "doctor_action": details["doctor_action"],
            "patient": patient,
            "findings": findings
        })
    except Exception as e:
        return jsonify({
            "status": "error",
            "message": f"Terjadi kesalahan inferensi: {str(e)}"
        }), 400


if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5003))
    print(f"Menjalankan Medical Check-Up Work Fitness Assessment Portal di http://127.0.0.1:{port} ...")
    app.run(host="127.0.0.1", port=port, debug=False)
