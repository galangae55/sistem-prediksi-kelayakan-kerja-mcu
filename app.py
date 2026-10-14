from flask import Flask, render_template, request
import numpy as np
import joblib

app = Flask(__name__)

import os

# Load model (cek direktori models/ atau current directory)
MODEL_PATH = os.path.join(os.path.dirname(__file__), 'models', 'health_status_model_v4.pkl')
if not os.path.exists(MODEL_PATH):
    MODEL_PATH = 'health_status_model_v4.pkl'

try:
    model = joblib.load(MODEL_PATH)
    print(f"Model berhasil dimuat dari: {MODEL_PATH}")
except Exception as e:
    model = None
    print(f"Peringatan: Model belum dapat dimuat ({e}). Pastikan file model ada di folder 'models/'.")

# Mapping prediksi ke label status kelaikan kerja
PREDICTION_LABELS = {
    0: "Fit to Work",
    1: "Fit with Note",
    2: "Fit with Restriction",
    3: "Unfit"
}

@app.route("/", methods=["GET", "POST"])
def index():
    result = None
    if request.method == "POST":
        try:
            # Collect input data from form
            form_data = [
                float(request.form["BMICat"]),
                float(request.form["Hipertensi_Kategori"]),
                float(request.form["tuberkulosis"]),
                float(request.form["penyakit_jantung"]),
                float(request.form["hipertensi"]),
                float(request.form["diabetes_melitus"]),
                float(request.form["gangguan_jiwa"]),
                float(request.form["trauma_pada_kepala"]),
                float(request.form["hepatitis"]),
                float(request.form["Spirometri"]),
                float(request.form["Treadmil"]),
                float(request.form["Audiometri"]),
                float(request.form["foto_thorax"]),
                float(request.form["Nadi(kali/menit)"]),
                float(request.form["FrekuensiNapas(kali/menit)"]),
                float(request.form["Tingkatan_Kesadaran"]),
                float(request.form["Buta_Warna"]),
                float(request.form["Jantung"]),
                float(request.form["hemoglobin"]),
                float(request.form["leukosit"]),
                float(request.form["eritrosit"]),
                float(request.form["LED"]),
                float(request.form["eosinofil"]),
                float(request.form["basofil"]),
                float(request.form["neutrofil"]),
                float(request.form["lymphosit"]),
                float(request.form["monosit"]),
                float(request.form["trombosit"]),
                float(request.form["hematokrit"]),
                float(request.form["MCV"]),
                float(request.form["SGOT"]),
                float(request.form["SGPT"]),
                float(request.form["BUN"]),
                float(request.form["kreatinin"]),
                float(request.form["asam_urat"]),
                float(request.form["kolestrol_total"]),
                float(request.form["trigliserida"]),
                float(request.form["kolestrol_HDL_(direct)"]),
                float(request.form["kolestrol_LDL_(direct)"]),
                float(request.form["gula_darah_puasa"]),
                float(request.form["gula_darah_2_jam_pp"]),
                float(request.form["anti_HbBs"]),
                float(request.form["HBs_Ag_Kuantitatif"]),
                float(request.form["pH_pada_urine"]),
                float(request.form["berat_jenis_pada_urine"]),
                float(request.form["nitrite_pada_urine"]),
                float(request.form["protein_pada_urine"]),
                float(request.form["reduksi_pada_urine"]),
                float(request.form["ketone_pada_urine"]),
                float(request.form["urobilinogen_pada_urine"]),
                float(request.form["billirubin_pada_urine"]),
                float(request.form["eritrosit_pada_urine"]),
                float(request.form["lekosit_pada_urine"]),
                float(request.form["silinder_pada_urine"]),
                float(request.form["kristal_pada_urine"]),
                float(request.form["yeast_pada_urine"]),
                float(request.form["bakteri_pada_urine"]),
            ]

            if model is None:
                result = "Model prediksi belum tersedia. Pastikan file model 'health_status_model_v4.pkl' sudah ada di folder 'models/' atau latih kembali melalui notebook."
            else:
                # Prepare data for prediction
                input_data = np.array([form_data]).reshape(1, -1)

                # Make the prediction
                prediction = model.predict(input_data)[0]  # Extract single value prediction
                prediction_label = PREDICTION_LABELS.get(prediction, "Unknown")
                result = f"The predicted health fitness status is: {prediction_label}"

        except Exception as e:
            result = f"Error in prediction: {e}"

    return render_template("index.html", result=result)


if __name__ == "__main__":
    app.run(debug=True)
