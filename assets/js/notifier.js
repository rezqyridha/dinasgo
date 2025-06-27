// notifier.js - Standarisasi Notifikasi untuk semua modul DinasGo
document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const msg = params.get("msg");
    const obj = params.get("obj") ?? "data"; // default: "data"

    const notifications = {
        // === SUCCESS MESSAGES ===
        success: {
            icon: "success",
            title: "Berhasil",
            text: `Operasi pada ${obj} berhasil dilakukan.`,
        },
        added: {
            icon: "success",
            title: "Data Ditambahkan",
            text: `${capitalize(obj)} berhasil ditambahkan.`,
        },
        updated: {
            icon: "success",
            title: "Data Diperbarui",
            text: `${capitalize(obj)} berhasil diperbarui.`,
        },
        deleted: {
            icon: "success",
            title: "Data Dihapus",
            text: `${capitalize(obj)} berhasil dihapus.`,
        },
        verifikasi_berhasil: {
            icon: "success",
            title: "Pengajuan Diverifikasi",
            text: `Status pengajuan berhasil diperbarui oleh admin.`,
        },
        verifikasi_atasan_berhasil: {
            icon: "success",
            title: "Verifikasi Atasan",
            text: `Pengajuan berhasil diverifikasi oleh atasan.`,
        },
        disetujui: {
            icon: "success",
            title: "Rincian Disetujui",
            text: "Rincian biaya berhasil disetujui oleh bendahara.",
        },
        diajukan: {
            icon: "success",
            title: "Rincian Diajukan",
            text: "Rincian biaya berhasil diajukan ke bendahara.",
        },

        // === ERROR / FAILED ===
        error: {
            icon: "error",
            title: "Gagal",
            text: `Terjadi kesalahan saat memproses ${obj}.`,
        },
        failed: {
            icon: "error",
            title: "Gagal Menyimpan",
            text: `${capitalize(obj)} tidak dapat disimpan ke database.`,
        },
        unauthorized: {
            icon: "error",
            title: "Akses Ditolak",
            text: `Anda tidak memiliki izin untuk mengakses ${obj}.`,
        },
        fk_blocked: {
            icon: "error",
            title: "Tidak Bisa Dihapus",
            text: `${capitalize(obj)} terkait dengan data lain (relasi aktif).`,
        },
        ditolak: {
            icon: "error",
            title: "Rincian Ditolak",
            text: "Rincian biaya telah ditolak oleh bendahara.",
        },

        // === WARNING ===
        kosong: {
            icon: "warning",
            title: "Form Tidak Lengkap",
            text: `Mohon lengkapi semua field pada ${obj}.`,
        },
        duplicate: {
            icon: "warning",
            title: "Duplikat",
            text: `${capitalize(obj)} sudah ada sebelumnya.`,
        },
        duplikat: {
            icon: "warning",
            title: "Data Duplikat",
            text: `${capitalize(obj)} sudah digunakan.`,
        },
        invalid: {
            icon: "warning",
            title: "Permintaan Tidak Valid",
            text: `${capitalize(obj)} tidak ditemukan atau parameter salah.`,
        },
        locked: {
            icon: "warning",
            title: "Terkunci",
            text: `${capitalize(
                obj
            )} tidak dapat diubah karena terhubung ke user.`,
        },
        notfound: {
            icon: "warning",
            title: "Data Tidak Ditemukan",
            text: "Rincian biaya tidak tersedia atau bukan status draft.",
        },
        nodetail: {
            icon: "warning",
            title: "Detail Kosong",
            text: "Minimal satu baris rincian biaya harus diisi sebelum diajukan.",
        },
        invalid: {
            icon: "warning",
            title: "Permintaan Tidak Valid",
            text: "Parameter tidak lengkap atau salah.",
        },

        // === INFO ===
        nochange: {
            icon: "info",
            title: "Tidak Ada Perubahan",
            text: `Tidak ada perubahan data pada ${obj}.`,
        },
    };

    if (msg && notifications[msg]) {
        Swal.fire({
            ...notifications[msg],
            timer: 2000,
            showConfirmButton: false,
        });

        // Hapus param ?msg & ?obj setelah tampil
        if (window.history.replaceState) {
            const cleanUrl = window.location.pathname;
            window.history.replaceState(null, null, cleanUrl);
        }
    }
});

// Fungsi helper untuk kapitalisasi
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Fungsi konfirmasi hapus standar
function confirmDelete(url) {
    Swal.fire({
        title: "Yakin ingin menghapus?",
        text: "Data ini akan dihapus secara permanen dan tidak bisa dikembalikan.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

window.notifier = {
    show: function (message, type = "info", timeout = 1000) {
        const alertDiv = document.createElement("div");
        alertDiv.className = `alert alert-${type} position-fixed start-50 translate-middle-x`;
        alertDiv.style.top = "58%";
        alertDiv.style.transform = "translate(-50%, -50%)";
        alertDiv.style.left = "50%";
        alertDiv.style.maxWidth = "400px";
        alertDiv.style.width = "auto";
        alertDiv.style.textAlign = "center";
        alertDiv.style.boxShadow = "0 2px 8px rgba(0,0,0,0.15)";
        alertDiv.style.padding = "1rem 1.5rem";
        alertDiv.style.borderRadius = "8px";
        alertDiv.style.zIndex = 9999;
        alertDiv.innerText = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), timeout);
    },
};
