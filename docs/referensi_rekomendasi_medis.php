<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LabController extends Controller
{
    /**
     * Menampilkan form input pemeriksaan laboratorium.
     */
    public function form()
    {
        return view('lab.form');
    }

    /**
     * Menerima data input, memeriksa setiap parameter, dan mengirim hasil pemeriksaan ke view.
     */
    public function periksa(Request $request)
    {
        // Ambil seluruh data input kecuali _token
        $data = $request->except('_token');
        // Pastikan gender dikirim (1: Pria, 2: Wanita)
        $gender = $request->input('gender');

        // Array untuk menampung pesan hasil pemeriksaan per parameter
        $hasilPemeriksaan = [];

        // Proses setiap input (skip field gender)
        foreach ($data as $key => $value) {
            if ($key == 'gender') {
                continue;
            }
            $pesan = $this->periksaAtribut($key, $value, $gender);
            if ($pesan !== null) {
                $hasilPemeriksaan[$key] = $pesan;
            }
        }

        return view('lab.hasil', compact('hasilPemeriksaan'));
    }

    /**
     * Fungsi private untuk memeriksa masing-masing atribut dan mengembalikan saran supportif.
     *
     * Parameter:
     * - $key: nama parameter (misal: 'hemoglobin', 'leukosit', dll)
     * - $value: nilai parameter
     * - $gender: 1 untuk pria, 2 untuk wanita
     *
     * @return string|null Pesan saran jika abnormal, null jika normal.
     */
    private function periksaAtribut($key, $value, $gender)
    {
        // Pastikan nilai berupa float
        $value = floatval($value);

        switch ($key) {
            // --------- Hemoglobin ---------
            case 'hemoglobin':
                if ($gender == 2) { // Wanita: 12 - 16 g/dL
                    if ($value < 12) {
                        return "Hasil hemoglobin Anda <strong>{$value} g/dL</strong> (normal: 12 - 16 g/dL). 
                        Kadar rendah dapat mengindikasikan risiko anemia.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Perbanyak makanan kaya zat besi (daging merah tanpa lemak, hati, bayam, kacang-kacangan).</li>
                            <li>Tambahkan vitamin C (misalnya jeruk, paprika) untuk membantu penyerapan zat besi.</li>
                            <li>Pastikan pola makan seimbang dan istirahat yang cukup.</li>
                        </ol>";
                    } elseif ($value > 16) {
                        return "Hasil hemoglobin Anda <strong>{$value} g/dL</strong> (normal: 12 - 16 g/dL). 
                        Kadar tinggi dapat disebabkan oleh dehidrasi atau kebiasaan merokok.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Pastikan hidrasi optimal (minimal 8 gelas air per hari).</li>
                            <li>Perhatikan faktor risiko seperti merokok, dan ubah kebiasaan jika perlu.</li>
                            <li>Ulangi pemeriksaan jika ada gejala lain seperti pusing atau lelah.</li>
                        </ol>";
                    }
                } else { // Pria: 13 - 17 g/dL
                    if ($value < 13) {
                        return "Hasil hemoglobin Anda <strong>{$value} g/dL</strong> (normal: 13 - 17 g/dL). 
                        Kadar rendah dapat mengindikasikan anemia.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Perbanyak konsumsi makanan kaya zat besi (daging merah, hati, sayuran hijau).</li>
                            <li>Tambahkan vitamin C untuk mendukung penyerapan zat besi.</li>
                            <li>Perhatikan asupan nutrisi secara keseluruhan.</li>
                        </ol>";
                    } elseif ($value > 17) {
                        return "Hasil hemoglobin Anda <strong>{$value} g/dL</strong> (normal: 13 - 17 g/dL). 
                        Kadar tinggi dapat diakibatkan oleh dehidrasi.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Pastikan cukup minum air.</li>
                            <li>Periksa ulang gaya hidup dan kebiasaan seperti merokok.</li>
                            <li>Amati adanya gejala lain dan ulangi pemeriksaan bila perlu.</li>
                        </ol>";
                    }
                }
                break;

            // --------- Leukosit ---------
            case 'leukosit':
                if ($value < 4000) {
                    return "Jumlah leukosit Anda <strong>{$value} sel/µL</strong> (normal: 4000 - 11000 sel/µL). 
                    Nilai rendah dapat terjadi akibat infeksi virus atau gangguan sumsum tulang.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Istirahat yang cukup dan perhatikan asupan nutrisi.</li>
                        <li>Hindari keramaian dan kontak dengan orang yang sakit.</li>
                        <li>Konsumsi buah dan sayuran untuk mendukung sistem kekebalan.</li>
                    </ol>";
                } elseif ($value > 11000) {
                    return "Jumlah leukosit Anda <strong>{$value} sel/µL</strong> (normal: 4000 - 11000 sel/µL). 
                    Nilai tinggi bisa menandakan adanya infeksi atau peradangan.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Perhatikan gejala seperti demam, nyeri, atau sakit tenggorokan.</li>
                        <li>Istirahat yang cukup dan konsumsi makanan bergizi.</li>
                        <li>Kurangi stres dengan teknik relaksasi.</li>
                    </ol>";
                }
                break;

            // --------- Eritrosit ---------
            case 'eritrosit':
                if ($gender == 2) { // Wanita: 4 - 5 juta sel/µL
                    if ($value < 4000000) {
                        return "Jumlah eritrosit Anda <strong>{$value}</strong> sel/µL (normal wanita: 4 - 5 juta). 
                        Kadar rendah dapat mengindikasikan anemia.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Perbanyak asupan zat besi dan vitamin B12.</li>
                            <li>Konsumsi makanan seperti daging, telur, dan sayuran hijau.</li>
                        </ol>";
                    } elseif ($value > 5000000) {
                        return "Jumlah eritrosit Anda <strong>{$value}</strong> sel/µL (normal wanita: 4 - 5 juta). 
                        Kadar tinggi bisa disebabkan oleh dehidrasi.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Pastikan hidrasi optimal dengan minum cukup air.</li>
                            <li>Amati kondisi tubuh secara keseluruhan.</li>
                        </ol>";
                    }
                } else { // Pria: 4.5 - 5.5 juta sel/µL
                    if ($value < 4500000) {
                        return "Jumlah eritrosit Anda <strong>{$value}</strong> sel/µL (normal pria: 4.5 - 5.5 juta). 
                        Kadar rendah dapat mengindikasikan anemia.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Pastikan konsumsi makanan kaya zat besi dan vitamin B12.</li>
                            <li>Perbanyak asupan protein dan nutrisi seimbang.</li>
                        </ol>";
                    } elseif ($value > 5500000) {
                        return "Jumlah eritrosit Anda <strong>{$value}</strong> sel/µL (normal pria: 4.5 - 5.5 juta). 
                        Kadar tinggi bisa disebabkan oleh dehidrasi.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Pastikan Anda cukup terhidrasi.</li>
                            <li>Perhatikan gejala lain dan ulangi pemeriksaan bila perlu.</li>
                        </ol>";
                    }
                }
                break;

            // --------- LED (Laju Endap Darah) ---------
            case 'LED':
                // Wanita: 0 - 20 mm/jam, Pria: 0 - 15 mm/jam
                if ($gender == 2 && ($value < 0 || $value > 20)) {
                    return "Nilai LED Anda <strong>{$value} mm/jam</strong> (normal wanita: 0 - 20). 
                    Nilai abnormal dapat menandakan peradangan atau infeksi.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Perbanyak konsumsi makanan anti-inflamasi (buah, sayur segar).</li>
                        <li>Jaga pola hidup sehat dengan olahraga teratur dan istirahat cukup.</li>
                    </ol>";
                } elseif ($gender == 1 && ($value < 0 || $value > 15)) {
                    return "Nilai LED Anda <strong>{$value} mm/jam</strong> (normal pria: 0 - 15). 
                    Kadar abnormal dapat menunjukkan adanya peradangan.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Konsumsi makanan bergizi dan antioksidan tinggi.</li>
                        <li>Pastikan istirahat cukup dan hindari stres.</li>
                    </ol>";
                }
                break;

            // --------- Eosinofil ---------
            case 'eosinofil':
                if ($value < 0 || $value > 1) {
                    return "Nilai eosinofil Anda <strong>{$value}</strong> (normal: 0 - 1). 
                    Penyimpangan bisa terkait reaksi alergi atau infeksi parasit.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Perhatikan apakah ada pemicu alergi di lingkungan Anda.</li>
                        <li>Konsumsi makanan tinggi antioksidan (buah beri, sayuran hijau).</li>
                    </ol>";
                }
                break;

            // --------- Basofil ---------
            case 'basofil':
                if ($value < 0 || $value > 1) {
                    return "Nilai basofil Anda <strong>{$value}</strong> (normal: 0 - 1). 
                    Meskipun jarang terjadi, penyimpangan bisa mengindikasikan reaksi alergi atau gangguan kronis.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Perhatikan gejala lain (misal ruam, demam ringan).</li>
                        <li>Pastikan asupan nutrisi dan hidrasi optimal.</li>
                    </ol>";
                }
                break;

            // --------- Neutrofil ---------
            case 'neutrofil':
                if ($value < 50 || $value > 70) {
                    return "Nilai neutrofil Anda <strong>{$value}%</strong> (normal: 50 - 70%). 
                    Penyimpangan dapat menandakan infeksi atau stres pada sistem imun.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Konsumsi makanan bergizi untuk mendukung sistem kekebalan (misalnya buah dan sayuran).</li>
                        <li>Pastikan istirahat yang cukup dan kelola stres.</li>
                    </ol>";
                }
                break;

            // --------- Limfosit ---------
            case 'limfosit':
                if ($value < 25 || $value > 33) {
                    return "Nilai limfosit Anda <strong>{$value}%</strong> (normal: 25 - 33%). 
                    Penyimpangan dapat menunjukkan infeksi virus atau gangguan imunitas.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Jaga pola makan seimbang dan kaya vitamin.</li>
                        <li>Hindari paparan infeksi dengan menjaga kebersihan diri.</li>
                    </ol>";
                }
                break;

            // --------- Monosit ---------
            case 'monosit':
                if ($value < 3 || $value > 8) {
                    return "Nilai monosit Anda <strong>{$value}%</strong> (normal: 3 - 8%). 
                    Penyimpangan bisa menunjukkan peradangan atau infeksi kronis.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Perbanyak konsumsi makanan dengan kandungan anti-inflamasi (misalnya kunyit, jahe).</li>
                        <li>Pastikan hidrasi yang cukup.</li>
                    </ol>";
                }
                break;

            // --------- Trombosit ---------
            case 'trombosit':
                if ($value < 150000 || $value > 400000) {
                    return "Jumlah trombosit Anda <strong>{$value} sel/µL</strong> (normal: 150.000 - 400.000 sel/µL). 
                    Penyimpangan dapat mempengaruhi pembekuan darah.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Konsumsi makanan kaya vitamin K (sayuran hijau seperti bayam, brokoli).</li>
                        <li>Pastikan asupan nutrisi dan cairan cukup.</li>
                    </ol>";
                }
                break;

            // --------- Hematokrit ---------
            case 'hematokrit':
                if ($gender == 2) { // Wanita: 37 - 45%
                    if ($value < 37 || $value > 45) {
                        return "Nilai hematokrit Anda <strong>{$value}%</strong> (normal wanita: 37 - 45%). 
                        Penyimpangan bisa disebabkan oleh anemia atau dehidrasi.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Pastikan asupan cairan yang cukup.</li>
                            <li>Perbanyak konsumsi zat besi dan vitamin B.</li>
                        </ol>";
                    }
                } else { // Pria: 40 - 50%
                    if ($value < 40 || $value > 50) {
                        return "Nilai hematokrit Anda <strong>{$value}%</strong> (normal pria: 40 - 50%). 
                        Penyimpangan bisa terjadi akibat dehidrasi.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Pastikan asupan cairan optimal.</li>
                            <li>Perhatikan pola makan secara keseluruhan.</li>
                        </ol>";
                    }
                }
                break;

            // --------- MCV ---------
            case 'MCV':
                if ($value < 82 || $value > 92) {
                    return "Nilai MCV Anda <strong>{$value} fL</strong> (normal: 82 - 92 fL). 
                    Nilai abnormal dapat membantu mendeteksi jenis anemia.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Perbanyak konsumsi vitamin B12 dan asam folat (daging, telur, sayuran hijau).</li>
                        <li>Pastikan pemeriksaan lanjutan jika gejala anemia muncul.</li>
                    </ol>";
                }
                break;

            // --------- SGOT ---------
            case 'SGOT':
                if ($gender == 2) { // Wanita: ≤31 U/L
                    if ($value < 0 || $value > 31) {
                        return "Nilai SGOT Anda <strong>{$value} U/L</strong> (normal wanita: ≤31 U/L). 
                        Kadar abnormal dapat menandakan gangguan pada hati atau otot.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Hindari konsumsi alkohol dan makanan berminyak.</li>
                            <li>Perbanyak konsumsi sayur dan buah segar untuk mendukung fungsi hati.</li>
                        </ol>";
                    }
                } else { // Pria: ≤37 U/L
                    if ($value < 0 || $value > 37) {
                        return "Nilai SGOT Anda <strong>{$value} U/L</strong> (normal pria: ≤37 U/L). 
                        Kadar abnormal bisa disebabkan oleh kerusakan hati atau otot.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Batasi konsumsi alkohol dan makanan berlemak.</li>
                            <li>Perbanyak konsumsi makanan detoks (sayuran hijau, buah-buahan).</li>
                        </ol>";
                    }
                }
                break;

            // --------- SGPT ---------
            case 'SGPT':
                if ($gender == 2) { // Wanita: ≤32 U/L
                    if ($value < 0 || $value > 32) {
                        return "Nilai SGPT Anda <strong>{$value} U/L</strong> (normal wanita: ≤32 U/L). 
                        Kadar abnormal dapat menandakan gangguan hati.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Kurangi konsumsi alkohol dan makanan berat/berlemak.</li>
                            <li>Perbanyak konsumsi sayuran dan buah segar.</li>
                        </ol>";
                    }
                } else { // Pria: ≤42 U/L
                    if ($value < 0 || $value > 42) {
                        return "Nilai SGPT Anda <strong>{$value} U/L</strong> (normal pria: ≤42 U/L). 
                        Penyimpangan bisa menandakan kerusakan sel hati.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Hindari makanan berlemak dan alkohol.</li>
                            <li>Perbanyak konsumsi makanan yang mendukung detoksifikasi hati (sayur hijau, buah-buahan).</li>
                        </ol>";
                    }
                }
                break;

            // --------- BUN ---------
            case 'BUN':
                if ($value < 5 || $value > 23.5) {
                    return "Nilai BUN Anda <strong>{$value} mg/dL</strong> (normal: 5 - 23.5 mg/dL). 
                    Kadar abnormal dapat mencerminkan gangguan fungsi ginjal atau metabolisme protein.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Pastikan asupan cairan optimal.</li>
                        <li>Atur asupan protein agar tidak berlebihan.</li>
                    </ol>";
                }
                break;

            // --------- Kreatinin ---------
            case 'kreatinin':
                if ($gender == 2) { // Wanita: 0.7 - 1.1 mg/dL
                    if ($value < 0.7 || $value > 1.1) {
                        return "Nilai kreatinin Anda <strong>{$value} mg/dL</strong> (normal wanita: 0.7 - 1.1 mg/dL). 
                        Kadar abnormal dapat menandakan gangguan fungsi ginjal.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Pastikan hidrasi yang cukup.</li>
                            <li>Kurangi asupan protein jika diperlukan.</li>
                        </ol>";
                    }
                } else { // Pria: 0.8 - 1.4 mg/dL
                    if ($value < 0.8 || $value > 1.4) {
                        return "Nilai kreatinin Anda <strong>{$value} mg/dL</strong> (normal pria: 0.8 - 1.4 mg/dL). 
                        Kadar tinggi bisa menunjukkan adanya gangguan ginjal.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Pastikan asupan cairan optimal.</li>
                            <li>Perhatikan asupan protein dan gaya hidup sehat.</li>
                        </ol>";
                    }
                }
                break;

            // --------- Asam Urat ---------
            case 'asam_urat':
                if ($gender == 2) { // Wanita: 2.6 - 6 mg/dL
                    if ($value < 2.6 || $value > 6) {
                        return "Nilai asam urat Anda <strong>{$value} mg/dL</strong> (normal wanita: 2.6 - 6 mg/dL). 
                        Kadar tinggi meningkatkan risiko gout.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Hindari makanan tinggi purin (misal: daging merah, makanan laut).</li>
                            <li>Perbanyak minum air putih.</li>
                            <li>Konsumsi buah seperti ceri yang dapat membantu menurunkan asam urat.</li>
                        </ol>";
                    }
                } else { // Pria: 3.5 - 7.2 mg/dL
                    if ($value < 3.5 || $value > 7.2) {
                        return "Nilai asam urat Anda <strong>{$value} mg/dL</strong> (normal pria: 3.5 - 7.2 mg/dL). 
                        Kadar tinggi meningkatkan risiko gout.
                        <br><br>
                        <strong>Saran saya:</strong>
                        <ol>
                            <li>Batasi makanan tinggi purin dan konsumsi alkohol.</li>
                            <li>Perbanyak asupan air putih.</li>
                            <li>Konsumsi buah yang dapat membantu menurunkan asam urat (misal: ceri).</li>
                        </ol>";
                    }
                }
                break;

            // --------- Kolesterol Total ---------
            case 'kolesterol_total':
                if ($value >= 200) {
                    return "Nilai kolesterol total Anda <strong>{$value} mg/dL</strong> (normal: <200 mg/dL). 
                    Kadar tinggi dapat meningkatkan risiko penyakit jantung.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Kurangi konsumsi makanan tinggi lemak jenuh (gorengan, makanan olahan).</li>
                        <li>Perbanyak konsumsi serat (buah, sayur, gandum utuh).</li>
                        <li>Lakukan aktivitas fisik rutin.</li>
                    </ol>";
                }
                break;

            // --------- Trigliserida ---------
            case 'trigliserida':
                if ($value >= 200) {
                    return "Nilai trigliserida Anda <strong>{$value} mg/dL</strong> (normal: <200 mg/dL). 
                    Kadar tinggi dapat meningkatkan risiko penyakit kardiovaskular.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Batasi konsumsi gula dan karbohidrat sederhana.</li>
                        <li>Perbanyak asupan asam lemak omega-3 (ikan salmon, kacang-kacangan).</li>
                        <li>Tingkatkan aktivitas fisik.</li>
                    </ol>";
                }
                break;

            // --------- Kolesterol HDL ---------
            case 'kolesterol_HDL':
                if ($value <= 35) {
                    return "Nilai kolesterol HDL Anda <strong>{$value} mg/dL</strong> (normal: >35 mg/dL). 
                    Kadar HDL yang rendah dapat meningkatkan risiko penyakit jantung.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Perbanyak olahraga aerobik (jalan cepat, bersepeda).</li>
                        <li>Konsumsi lemak sehat (minyak zaitun, alpukat, kacang-kacangan).</li>
                        <li>Hindari lemak trans dan jenuh.</li>
                    </ol>";
                }
                break;

            // --------- Kolesterol LDL ---------
            case 'kolesterol_LDL':
                if ($value >= 115) {
                    return "Nilai kolesterol LDL Anda <strong>{$value} mg/dL</strong> (normal: <115 mg/dL). 
                    Kadar LDL yang tinggi dapat menyebabkan penumpukan plak di arteri.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Kurangi konsumsi lemak jenuh.</li>
                        <li>Perbanyak konsumsi serat dan lemak sehat.</li>
                        <li>Lakukan olahraga secara teratur.</li>
                    </ol>";
                }
                break;

            // --------- Gula Darah 2 Jam PP ---------
            case 'gula_darah_2_jam_pp':
                if ($value >= 140) {
                    return "Nilai gula darah 2 jam PP Anda <strong>{$value} mg/dL</strong> (normal: <140 mg/dL). 
                    Hasil ini menunjukkan adanya gangguan toleransi glukosa.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Hindari konsumsi gula berlebih dan makanan dengan indeks glikemik tinggi.</li>
                        <li>Perbanyak asupan serat dan protein berkualitas.</li>
                        <li>Lakukan aktivitas fisik secara rutin.</li>
                    </ol>";
                }
                break;

            // --------- pH Urine ---------
            case 'pH_urine':
                if ($value < 5 || $value > 8) {
                    return "Nilai pH urine Anda <strong>{$value}</strong> (normal: 5 - 8). 
                    Penyimpangan dapat mengindikasikan infeksi saluran kemih atau gangguan metabolisme.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Pastikan asupan cairan optimal.</li>
                        <li>Konsumsi makanan seimbang.</li>
                    </ol>";
                }
                break;

            // --------- Berat Jenis Urine ---------
            case 'berat_jenis_urine':
                if ($value < 1.005 || $value > 1.030) {
                    return "Nilai berat jenis urine Anda <strong>{$value}</strong> (normal: 1.005 - 1.030). 
                    Penyimpangan ini bisa menandakan dehidrasi atau gangguan elektrolit.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Pastikan asupan cairan cukup.</li>
                        <li>Perhatikan asupan garam dan elektrolit.</li>
                    </ol>";
                }
                break;

            // --------- Eritrosit Urine ---------
            case 'eritrosit_urine':
                if ($value > 2) {
                    return "Jumlah eritrosit dalam urine Anda <strong>{$value}</strong> (normal: 0 - 2). 
                    Kenaikan ini dapat mengindikasikan iritasi atau infeksi saluran kemih.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Perbanyak minum air putih untuk membantu membersihkan saluran kemih.</li>
                        <li>Perhatikan gejala seperti nyeri saat buang air kecil.</li>
                    </ol>";
                }
                break;

            // --------- Leukosit Urine ---------
            case 'leukosit_urine':
                if ($value > 5) {
                    return "Jumlah leukosit dalam urine Anda <strong>{$value}</strong> (normal: 0 - 5). 
                    Kadar tinggi dapat mengindikasikan infeksi saluran kemih.
                    <br><br>
                    <strong>Saran saya:</strong>
                    <ol>
                        <li>Pastikan hidrasi cukup.</li>
                        <li>Perhatikan gejala seperti sensasi terbakar saat buang air kecil.</li>
                        <li>Konsumsi makanan yang mendukung kesehatan saluran kemih (misal: jus cranberry).</li>
                    </ol>";
                }
                break;

            default:
                return null;
        }

        // Jika nilai parameter berada dalam batas normal, kembalikan null.
        return null;
    }
}
