import '@fontsource/ibm-plex-sans/400.css';
import '@fontsource/ibm-plex-sans/500.css';
import '@fontsource/ibm-plex-sans/600.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../css/app.css';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

const formatBytes = (bytes) => {
    if (bytes < 1024) return `${bytes} B`;
    const units = ['KB', 'MB', 'GB'];
    let value = bytes / 1024, i = 0;
    while (value >= 1024 && i < units.length - 1) { value /= 1024; i++; }
    return `${value.toFixed(2)} ${units[i]}`;
};

document.addEventListener('DOMContentLoaded', () => {
    // Sidebar (mobile)
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            sidebar?.classList.toggle('open');
            backdrop?.classList.toggle('show');
        });
    });
    backdrop?.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        backdrop.classList.remove('show');
    });

    // Toast
    document.querySelectorAll('.toast').forEach((el) => new bootstrap.Toast(el, { delay: 5000 }).show());

    // Lihat/sembunyikan password
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.querySelector(btn.dataset.togglePassword);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    // Drag and drop upload
    document.querySelectorAll('[data-dropzone]').forEach((zone) => {
        const input = zone.querySelector('input[type=file]');
        const info = zone.querySelector('[data-file-info]');
        const empty = zone.querySelector('[data-file-empty]');

        const render = () => {
            const file = input.files[0];
            if (!file) {
                info.classList.add('d-none');
                empty.classList.remove('d-none');
                return;
            }
            zone.querySelector('[data-file-name]').textContent = file.name;
            zone.querySelector('[data-file-size]').textContent = formatBytes(file.size);
            zone.querySelector('[data-file-type]').textContent =
                (file.name.split('.').pop() || '-').toUpperCase();
            info.classList.remove('d-none');
            empty.classList.add('d-none');
        };

        ['dragenter', 'dragover'].forEach((ev) => zone.addEventListener(ev, (e) => {
            e.preventDefault();
            zone.classList.add('dragover');
        }));
        ['dragleave', 'drop'].forEach((ev) => zone.addEventListener(ev, (e) => {
            e.preventDefault();
            zone.classList.remove('dragover');
        }));
        zone.addEventListener('drop', (e) => {
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                render();
            }
        });
        input.addEventListener('change', render);
        render();
    });

    // Dialog konfirmasi (hapus, nonaktifkan, dll.)
    const modalEl = document.getElementById('confirmModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        let pendingForm = null;

        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (e) => {
                if (form.dataset.confirmed === '1') return;
                e.preventDefault();
                pendingForm = form;
                modalEl.querySelector('[data-confirm-title]').textContent = form.dataset.confirmTitle || 'Konfirmasi';
                modalEl.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
                modalEl.querySelector('[data-confirm-ok]').textContent = form.dataset.confirmOk || 'Ya, lanjutkan';
                modal.show();
            });
        });

        modalEl.querySelector('[data-confirm-ok]').addEventListener('click', () => {
            if (pendingForm) {
                pendingForm.dataset.confirmed = '1';
                modal.hide();
                pendingForm.requestSubmit();
            }
        });
    }

    // Indikator loading pada proses panjang (enkripsi/dekripsi)
    document.querySelectorAll('form[data-loading]').forEach((form) => {
        form.addEventListener('submit', () => {
            if (!form.checkValidity()) return;
            document.getElementById('loadingOverlay')?.classList.add('show');
            form.querySelectorAll('button[type=submit]').forEach((b) => { b.disabled = true; });
        });
    });
});
