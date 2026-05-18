# 🔊 Audio File untuk Modul SSE Antrian

## File yang Dibutuhkan

```
ding-dong.mp3
```

## Cara Mendapatkan Audio

### Opsi 1: Download Gratis
1. Buka situs audio gratis:
   - https://pixabay.com/sound-effects/search/ding-dong/
   - https://freesound.org/search/?q=ding+dong
   - https://mixkit.co/free-sound-effects/notification/

2. Download audio "ding dong" atau "notification bell"

3. Edit audio (opsional):
   - Buka https://audiotrimmer.com/ (online editor)
   - Upload file audio
   - Potong durasi menjadi ~2 detik
   - Export sebagai MP3

4. Rename file menjadi `ding-dong.mp3`

5. Upload ke folder ini: `public/assets/audio/ding-dong.mp3`

### Opsi 2: Gunakan Audio Default Browser
Jika tidak ada file audio, sistem tetap berfungsi dengan Web Speech API saja (tanpa ting-tong sound).

## Spesifikasi Audio

- **Format:** MP3
- **Durasi:** 1-3 detik (recommended: 2 detik)
- **Size:** < 100 KB
- **Quality:** 128 kbps sudah cukup

## Testing

Setelah upload file, test di:
```
http://localhost:8000/papan-antrian
```

Klik di halaman, lalu tunggu admin memanggil antrian.
Anda harus mendengar:
1. Ding-dong sound 🔊
2. Suara TTS: "Nomor antrian X. Nama. Silakan masuk." 🗣️

## Troubleshooting

### Audio tidak terdengar?
- Pastikan file ada di `public/assets/audio/ding-dong.mp3`
- Cek volume browser/device
- Klik di halaman terlebih dahulu (user gesture required)
- Gunakan Chrome/Edge untuk hasil terbaik

### File tidak ditemukan?
- Cek path: `public/assets/audio/ding-dong.mp3`
- Cek nama file (case-sensitive)
- Refresh halaman (Ctrl+F5)

---

**Note:** File audio ini opsional. Sistem tetap berfungsi tanpa audio file (hanya Web Speech API).
