// notifier.js - Standarisasi Notifikasi SweetAlert untuk DinasGo

document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const msg = params.get("msg");
    const obj = params.get("obj") ?? "data"; // default fallback

    const notifications = {
        // === SUCCESS ===
        success: {
            icon: "success",
            title: "Berhasil",
            text: `${capitalize(obj)} berhasil diproses.`,
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
        verified_draft: {
            icon: "success",
            title: "Verifikasi Cairkan",
            text: `Pencairan dana berhasil diverifikasi oleh bendahara.`,
        },
        finalized: {
            icon: "success",
            title: "Finalisasi Berhasil",
            text: `Pencairan dana telah difinalisasi oleh admin.`,
        },
        diajukan: {
            icon: "success",
            title: "Diajukan",
            text: `${capitalize(obj)} berhasil diajukan.`,
        },
        disetujui: {
            icon: "success",
            title: "Disetujui",
            text: `${capitalize(obj)} berhasil disetujui.`,
        },

        // === ERROR ===
        error: {
            icon: "error",
            title: "Gagal",
            text: `Terjadi kesalahan pada ${obj}.`,
        },
        unauthorized: {
            icon: "error",
            title: "Akses Ditolak",
            text: `Anda tidak memiliki izin mengakses ${obj}.`,
        },
        failed: {
            icon: "error",
            title: "Gagal Menyimpan",
            text: `${capitalize(obj)} tidak dapat disimpan.`,
        },
        fk_blocked: {
            icon: "error",
            title: "Tidak Bisa Dihapus",
            text: `${capitalize(obj)} terhubung ke data lain.`,
        },
        forbidden_status: {
            icon: "error",
            title: "Status Tidak Valid",
            text: `Status ${obj} tidak dapat diverifikasi.`,
        },

        // === WARNING ===
        invalid_id: {
            icon: "warning",
            title: "ID Tidak Valid",
            text: `Parameter ID ${obj} tidak valid.`,
        },
        not_found: {
            icon: "warning",
            title: "Data Tidak Ditemukan",
            text: `${capitalize(obj)} tidak ditemukan.`,
        },
        invalid_date: {
            icon: "warning",
            title: "Tanggal Tidak Valid",
            text: `Tanggal yang dimasukkan tidak valid.`,
        },
        duplicate: {
            icon: "warning",
            title: "Duplikat Data",
            text: `${capitalize(obj)} sudah ada.`,
        },
        nochange: {
            icon: "info",
            title: "Tidak Ada Perubahan",
            text: `${capitalize(obj)} tidak mengalami perubahan.`,
        },
    };

    if (msg && notifications[msg]) {
        Swal.fire({
            ...notifications[msg],
            timer: 2000,
            showConfirmButton: false,
        });

        // Bersihkan param ?msg & ?obj di URL
        if (window.history.replaceState) {
            const cleanUrl = window.location.pathname;
            window.history.replaceState(null, null, cleanUrl);
        }
    }
});

// Helper kapitalisasi huruf pertama
function capitalize(str) {
    if (!str) return "";
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Konfirmasi hapus standar
function confirmDelete(url) {
    Swal.fire({
        title: "Yakin ingin menghapus?",
        text: "Data ini akan dihapus permanen & tidak dapat dikembalikan.",
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

// Fallback manual jika mau panggil custom
window.notifier = {
    show: function (message, type = "info", timeout = 1500) {
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
