# Dokumentasi Model Machine Learning

Direktori ini digunakan untuk menyimpan bobot model Machine Learning yang telah dilatih.

## Model Utama
* **File:** `health_status_model_v4.pkl`
* **Algoritma:** Random Forest Classifier (Ensemble Learning)
* **Jumlah Fitur Input:** 57 Fitur Medis Terpilih (Kategori BMI, Hipertensi, Profil Hematologi Darah Lengkap, Fungsi Hati SGOT/SGPT, Fungsi Ginjal BUN/Kreatinin, Glukosa Darah, dan Urinalisis Lengkap).
* **Target Output:** 4 Kelas Status Kelaikan Kerja:
  * `0`: Fit to Work
  * `1`: Fit with Note
  * `2`: Fit with Restriction
  * `3`: Unfit

## Informasi Ukuran File & Git
* File `health_status_model_v4.pkl` memiliki ukuran ~362 MB, sehingga otomatis dikecualikan dari repositori melalui file `.gitignore` agar proses `git push` ke GitHub tidak terblokir limit 100 MB.
* Secara lokal di komputer Anda, file ini tetap tersimpan di folder `models/` dan langsung digunakan oleh `app.py`.

## Cara Melatih Ulang Model
Jika file model belum tersedia atau ingin melatih ulang dengan hyperparameter baru:
1. Buka notebook: `notebooks/02_rule_based_labeling.ipynb`.
2. Jalankan seluruh sel untuk melakukan encoding data dan pelatihan model (`RandomForestClassifier`).
3. Sel terakhir akan menyimpan model ke `models/health_status_model_v4.pkl` menggunakan `joblib.dump()`.
