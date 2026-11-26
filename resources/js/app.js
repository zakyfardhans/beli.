import './bootstrap';

const productGallery = document.querySelector('[data-product-gallery]');

if (productGallery) {
    const inlineImage = productGallery.querySelector('[data-gallery-main]');
    const inlineThumbs = [...productGallery.querySelectorAll('[data-gallery-thumb]')];
    const strip = productGallery.querySelector('[data-gallery-strip]');
    const stripButtons = [...productGallery.querySelectorAll('[data-gallery-strip-scroll]')];
    const modal = document.querySelector('[data-gallery-modal]');
    const dialog = modal?.querySelector('[data-gallery-dialog]');
    const largeImage = modal?.querySelector('[data-gallery-large-image]');
    const modalCounter = modal?.querySelector('[data-gallery-modal-counter]');
    const inlineCounter = productGallery.querySelector('[data-gallery-counter]');
    const modalThumbs = [...(modal?.querySelectorAll('[data-gallery-modal-thumb]') ?? [])];
    const images = modalThumbs.map((thumb) => ({
        src: thumb.querySelector('img')?.src ?? '',
        alt: thumb.querySelector('img')?.alt ?? '',
    }));

    if (modal && dialog && largeImage && images.length > 0) {
        let currentIndex = 0;
        let trigger = null;
        let previousBodyOverflow = '';

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const scrollBehavior = prefersReducedMotion ? 'auto' : 'smooth';

        const updateSelection = (index) => {
            currentIndex = (index + images.length) % images.length;
            const image = images[currentIndex];

            if (inlineImage) {
                inlineImage.src = image.src;
                inlineImage.alt = image.alt;
            }

            largeImage.src = image.src;
            largeImage.alt = image.alt;

            if (inlineCounter) {
                inlineCounter.textContent = `${currentIndex + 1} dari ${images.length}`;
            }

            if (modalCounter) {
                modalCounter.textContent = `Foto ${currentIndex + 1} dari ${images.length}`;
            }

            [...inlineThumbs, ...modalThumbs].forEach((thumb) => {
                const active = Number(thumb.dataset.galleryIndex) === currentIndex;
                thumb.setAttribute('aria-pressed', String(active));
                thumb.classList.toggle('border-brand', active);
                thumb.classList.toggle('border-line', !active);
                thumb.classList.toggle('ring-2', active);
                thumb.classList.toggle('ring-brand/20', active);

                if (!active) {
                    thumb.classList.remove('ring-brand/20');
                }
            });

            const activeModalThumb = modalThumbs[currentIndex];
            activeModalThumb?.scrollIntoView({
                block: 'nearest',
                inline: 'nearest',
                behavior: scrollBehavior,
            });
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = previousBodyOverflow;
            document.removeEventListener('keydown', onKeydown);
            trigger?.focus();
        };

        const onKeydown = (event) => {
            if (event.key === 'Escape') {
                closeModal();
                return;
            }

            if (event.key === 'ArrowLeft') {
                updateSelection(currentIndex - 1);
                event.preventDefault();
                return;
            }

            if (event.key === 'ArrowRight') {
                updateSelection(currentIndex + 1);
                event.preventDefault();
                return;
            }

            if (event.key === 'Tab') {
                const focusable = [...dialog.querySelectorAll(
                    'button:not([disabled]), [href], input:not([disabled]), [tabindex]:not([tabindex="-1"])',
                )].filter((element) => !element.hidden);
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (event.shiftKey && document.activeElement === first) {
                    last?.focus();
                    event.preventDefault();
                } else if (!event.shiftKey && document.activeElement === last) {
                    first?.focus();
                    event.preventDefault();
                }
            }
        };

        const openModal = (element) => {
            trigger = element;
            previousBodyOverflow = document.body.style.overflow;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            updateSelection(currentIndex);
            modal.querySelector('[data-gallery-close]')?.focus();
            document.addEventListener('keydown', onKeydown);
        };

        const updateStripControls = () => {
            if (!strip) {
                return;
            }

            const canScroll = strip.scrollWidth > strip.clientWidth + 1;

            stripButtons.forEach((button) => {
                const isPrevious = button.dataset.galleryStripScroll === 'prev';
                button.hidden = !canScroll;
                button.disabled = isPrevious ? strip.scrollLeft <= 1 : strip.scrollLeft + strip.clientWidth >= strip.scrollWidth - 1;
            });
        };

        productGallery.querySelector('[data-gallery-open]')?.addEventListener('click', (event) => {
            openModal(event.currentTarget);
        });

        inlineThumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => updateSelection(Number(thumb.dataset.galleryIndex)));
        });

        stripButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (!strip) {
                    return;
                }

                const direction = button.dataset.galleryStripScroll === 'next' ? 1 : -1;
                strip.scrollBy({ left: direction * strip.clientWidth * 0.75, behavior: scrollBehavior });
            });
        });

        strip?.addEventListener('scroll', updateStripControls, { passive: true });
        window.addEventListener('resize', updateStripControls);

        modalThumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => updateSelection(Number(thumb.dataset.galleryIndex)));
        });

        modal.querySelector('[data-gallery-close]')?.addEventListener('click', closeModal);
        modal.querySelector('[data-gallery-prev]')?.addEventListener('click', () => updateSelection(currentIndex - 1));
        modal.querySelector('[data-gallery-next]')?.addEventListener('click', () => updateSelection(currentIndex + 1));
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        updateSelection(0);
        updateStripControls();
    }
}

document.querySelectorAll('[data-gallery-uploader]').forEach((input) => {
    const form = input.closest('form');
    const countLabel = form?.querySelector('[data-gallery-upload-count]');
    const existingCount = Number(input.dataset.currentCount ?? 0);

    if (!form || !countLabel) {
        return;
    }

    const updateUploadLimit = () => {
        const removedCount = form.querySelectorAll('input[name="hapus_gambar[]"]:checked').length;
        const remainingCount = Math.max(0, 10 - existingCount + removedCount);
        const tooManyFiles = input.files.length > remainingCount;

        input.setCustomValidity(
            tooManyFiles ? `Pilih maksimal ${remainingCount} gambar baru untuk galeri ini.` : '',
        );
        countLabel.textContent = tooManyFiles
            ? `Pilih maksimal ${remainingCount} gambar baru.`
            : `Dapat menambahkan ${remainingCount} gambar lagi.`;
    };

    input.addEventListener('change', updateUploadLimit);
    form.querySelectorAll('input[name="hapus_gambar[]"]').forEach((checkbox) => {
        checkbox.addEventListener('change', updateUploadLimit);
    });

    updateUploadLimit();
});

document.querySelectorAll('[data-product-variant-form]').forEach((form) => {
    const variantRows = form.querySelector('[data-variant-rows]');
    const variantTemplate = form.querySelector('[data-variant-template]');
    const sizeRows = form.querySelector('[data-size-guide-rows]');
    const sizeTemplate = form.querySelector('[data-size-guide-template]');
    const stockInput = form.querySelector('[data-product-stock-input]');
    const stockHelp = form.querySelector('[data-product-stock-help]');
    const totalLabel = form.querySelector('[data-variant-total]');
    const nextIndex = (container, rowSelector) => Math.max(
        -1,
        ...[...(container?.querySelectorAll(rowSelector) ?? [])].map((row) => {
            const name = row.querySelector('input[name]')?.name ?? '';
            const match = name.match(/\[(\d+)\]/);
            return match ? Number(match[1]) : -1;
        }),
    ) + 1;
    let nextVariantIndex = nextIndex(variantRows, '[data-variant-row]');
    let nextSizeIndex = nextIndex(sizeRows, '[data-size-guide-row]');

    const updateVariantStock = () => {
        const rows = [...(variantRows?.querySelectorAll('[data-variant-row]') ?? [])];
        const hasVariantInput = rows.some((row) => [...row.querySelectorAll('[data-variant-field]')]
            .some((field) => field.value.trim() !== ''));

        if (!stockInput || !stockHelp || !totalLabel) {
            return;
        }

        if (!hasVariantInput) {
            stockInput.readOnly = false;
            stockHelp.textContent = 'Jika variasi diisi, stok total dihitung dari stok setiap kombinasi.';
            totalLabel.textContent = 'Produk ini menggunakan satu stok tanpa variasi.';
            return;
        }

        const total = rows.reduce((sum, row) => {
            const size = row.querySelector('[data-variant-field="ukuran"]')?.value.trim();
            const color = row.querySelector('[data-variant-field="warna"]')?.value.trim();
            const stock = row.querySelector('[data-variant-field="stok"]')?.value;

            return size && color && stock !== '' ? sum + Number(stock) : sum;
        }, 0);

        stockInput.value = String(total);
        stockInput.readOnly = true;
        stockHelp.textContent = 'Stok produk dihitung otomatis dari semua kombinasi ukuran dan warna.';
        totalLabel.textContent = `Total stok seluruh variasi: ${total}.`;
    };

    const addRow = (container, template, index) => {
        if (!container || !template) {
            return;
        }

        const markup = template.innerHTML.replace(/__INDEX__/g, String(index));
        container.insertAdjacentHTML('beforeend', markup);
        container.lastElementChild?.querySelector('input')?.focus();
    };

    form.querySelector('[data-add-variant]')?.addEventListener('click', () => {
        addRow(variantRows, variantTemplate, nextVariantIndex++);
        updateVariantStock();
    });

    form.querySelector('[data-add-size-guide]')?.addEventListener('click', () => {
        addRow(sizeRows, sizeTemplate, nextSizeIndex++);
    });

    form.addEventListener('click', (event) => {
        const removeVariant = event.target.closest('[data-remove-variant]');
        const removeSizeGuide = event.target.closest('[data-remove-size-guide]');

        if (removeVariant) {
            removeVariant.closest('[data-variant-row]')?.remove();
            updateVariantStock();
        }

        if (removeSizeGuide) {
            removeSizeGuide.closest('[data-size-guide-row]')?.remove();
        }
    });

    variantRows?.addEventListener('input', updateVariantStock);
    updateVariantStock();
});

document.querySelectorAll('[data-variant-picker]').forEach((picker) => {
    const dataElement = picker.querySelector('[data-variant-data]');
    const choices = picker.querySelector('[data-variant-choices]');
    const fallback = picker.querySelector('[data-variant-select-fallback]');
    const form = picker.closest('form');
    const stockLabel = picker.querySelector('[data-variant-stock]');
    const errorLabel = picker.querySelector('[data-variant-error]');
    const selects = {
        ukuran: picker.querySelector('[data-variant-select="ukuran"]'),
        warna: picker.querySelector('[data-variant-select="warna"]'),
    };
    const quantity = form?.querySelector('[name="jumlah"]');

    if (!dataElement || !choices || !fallback || !form || !selects.ukuran || !selects.warna || !quantity) {
        return;
    }

    const variants = JSON.parse(dataElement.textContent);
    const options = [...picker.querySelectorAll('[data-variant-choice]')];
    const baseStock = Number(quantity.dataset.productStock ?? quantity.max);

    const findVariant = () => variants.find((variant) => (
        variant.ukuran === selects.ukuran.value
        && variant.warna === selects.warna.value
    ));

    const updateChoices = () => {
        options.forEach((button) => {
            const field = button.dataset.variantChoice;
            const otherField = field === 'ukuran' ? 'warna' : 'ukuran';
            const value = button.dataset.value;
            const otherValue = selects[otherField].value;
            const tersedia = variants.some((variant) => (
                variant[field] === value
                && variant.stok > 0
                && (!otherValue || variant[otherField] === otherValue)
            ));
            const selected = selects[field].value === value;

            button.disabled = !tersedia;
            button.setAttribute('aria-pressed', String(selected));
            button.classList.toggle('border-brand', selected);
            button.classList.toggle('bg-brand', selected);
            button.classList.toggle('text-white', selected);
            button.classList.toggle('ring-2', selected);
            button.classList.toggle('ring-brand/20', selected);
            button.classList.toggle('border-line', !selected);
            button.classList.toggle('bg-white', !selected);
            button.classList.toggle('text-ink', !selected);
        });

        const selectedVariant = findVariant();
        const stokCukup = selectedVariant && selectedVariant.stok >= Number(quantity.value || 1);
        const pilihanValid = selectedVariant && selectedVariant.stok > 0;

        quantity.max = selectedVariant ? String(selectedVariant.stok) : String(baseStock);
        quantity.setCustomValidity(
            selectedVariant && !stokCukup
                ? `Stok pilihan ini hanya ${selectedVariant.stok}. Kurangi jumlah.`
                : '',
        );

        if (stockLabel) {
            stockLabel.textContent = pilihanValid
                ? `Stok untuk pilihan ini: ${selectedVariant.stok}.`
                : 'Pilih ukuran dan warna untuk melihat stok tersedia.';
        }

        if (errorLabel && pilihanValid) {
            errorLabel.hidden = true;
        }
    };

    options.forEach((button) => {
        button.addEventListener('click', () => {
            const field = button.dataset.variantChoice;
            const otherField = field === 'ukuran' ? 'warna' : 'ukuran';
            selects[field].value = button.dataset.value;

            if (!variants.some((variant) => (
                variant[field] === selects[field].value
                && variant[otherField] === selects[otherField].value
                && variant.stok > 0
            ))) {
                selects[otherField].value = '';
            }

            updateChoices();
        });
    });

    Object.values(selects).forEach((select) => {
        select.required = false;
        select.hidden = true;
        select.addEventListener('change', updateChoices);
    });

    quantity.addEventListener('input', updateChoices);
    form.addEventListener('submit', (event) => {
        const selectedVariant = findVariant();

        if (!selectedVariant || selectedVariant.stok < Number(quantity.value || 1)) {
            event.preventDefault();

            if (errorLabel) {
                errorLabel.hidden = false;
                errorLabel.textContent = selectedVariant
                    ? `Stok pilihan ini hanya ${selectedVariant.stok}. Kurangi jumlah sebelum melanjutkan.`
                    : 'Pilih ukuran dan warna yang tersedia sebelum melanjutkan.';
            }

            const firstAvailable = options.find((button) => (
                button.dataset.variantChoice === 'ukuran' && !button.disabled
            ));

            if (!selectedVariant) {
                firstAvailable?.focus();
            } else {
                quantity.focus();
            }
        }
    });

    fallback.hidden = true;
    choices.hidden = false;
    updateChoices();
});
