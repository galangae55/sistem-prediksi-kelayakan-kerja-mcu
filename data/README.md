# Dokumentasi Dataset

Direktori ini berisi dataset yang digunakan dalam penelitian dan pengembangan model prediksi kelaikan kesehatan kerja (Fit-to-Work).

## Struktur File Dataset

### 1. Dataset yang Tersedia di Repositori
* **`sample/`**: Kumpulan sampel dataset berukuran kecil (<2 MB) untuk pengujian cepat alur pipeline:
  * `Realistic_Health_Screening_Data.csv`: Data skrining medis dengan distribusi probabilitas klinis realistis.
  * `New_Updated_Health_Screening_Data.csv`: Data dengan penambahan parameter tanda vital dan organ.
  * `Health_Screening_Labeled_Numeric_v2.csv`: Subset data ter-encoding numerik.
  * `BMI_Health_Screening_Data_Expanded_v2.csv`: Data perluasan atribut BMI.
* **`Health_Screening_Fully_Labeled_v3.csv`** (~4.7 MB):
  * Dataset lengkap dengan label status kelaikan kerja teks (`Fit to Work`, `Fit with Note`, `Fit with Restriction`, `Unfit`) yang dihasilkan dari *rule-based clinical evaluation*.

### 2. Dataset Skala Besar (Diabaikan oleh `.gitignore`)
Untuk mematuhi batasan ukuran file GitHub (<100 MB), file data mentah berukuran besar tidak di-push ke git:
* `Health_Screening_Labeled_Numeric.csv` (~188 MB)
* `dataset_berat_jenis_updated.csv` (~489 MB)

> **Catatan Reproduksi Data:**
> Anda dapat menghasilkan kembali dataset numerik lengkap dengan menjalankan notebook:
> `notebooks/01_preprocessing.ipynb` dan `notebooks/02_rule_based_labeling.ipynb`.
