function tcc_showTabContent(index) {
    const tabs = document.querySelectorAll('.tcc-tab');
    const contents = document.querySelectorAll('.tcc-tab-content');

    tabs.forEach(tab => tab.classList.remove('active'));
    contents.forEach(content => content.classList.remove('active'));

    tabs[index].classList.add('active');
    contents[index].classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
    const tcc_rates = {
        sea: {
            normal: parseFloat(tccVars.normal_sea_rate),
            copy: parseFloat(tccVars.copy_sea_rate),
            liquid: parseFloat(tccVars.liquid_sea_rate),
            battery: parseFloat(tccVars.battery_sea_rate)
        },
        air: {
            normal: parseFloat(tccVars.normal_air_rate),
            copy: parseFloat(tccVars.copy_air_rate),
            liquid: parseFloat(tccVars.liquid_air_rate),
            battery: parseFloat(tccVars.battery_air_rate)
        }
    };

    const tcc_containerRates = {
        "20ft": 33, // حجم کانتینر ۲۰ فوت (متر مکعب)
        "40ft": 67 // حجم کانتینر ۴۰ فوت (متر مکعب)
    };

    const tcc_clearance = parseFloat(tccVars.clearance); // هزینه ترخیص (¥)
    const tcc_fee = parseFloat(tccVars.fee); // هزینه ثابت
    const tcc_price_per_20ft_container = parseFloat(tccVars.price_per_20ft_container);
    const tcc_price_per_40ft_container = parseFloat(tccVars.price_per_40ft_container);
    
    const tcc_boxesForm = document.getElementById('tcc-boxes-form');
    const tcc_containerForm = document.getElementById('tcc-container-form');

    const tcc_boxesResult = document.getElementById('tcc-boxes-result');
    const tcc_containerResult = document.getElementById('tcc-container-result');

    function tcc_displayError(resultElement, message) {
        resultElement.innerHTML = `
            <h5 class="tcc-text-success">هزینه حمل‌ونقل:</h5>
            <h3 class="tcc-text-danger" style="font-family: 'Arial', sans-serif;">${message}</h3>
        `;
    }

    function tcc_focusFirstField() {
        const activeTab = document.querySelector('.tcc-tab-content.active form');
        if (activeTab) {
            const firstField = activeTab.querySelector('input, select');
            if (firstField) firstField.focus();
        }
    }

    function tcc_displayFirstMessage(form) {
        const resultElement = form.querySelector('.tcc-result-card');

        if (form.id === 'tcc-boxes-form') {
            tcc_displayError(resultElement, "لطفاً طول را وارد کنید.");
        } else if (form.id === 'tcc-container-form') {
            tcc_displayError(resultElement, "لطفاً نوع کانتینر را انتخاب کنید.");
        }
    }

    function tcc_setFirstFieldFocus(form) {
        const firstField = form.querySelector('input, select');
        if (firstField) {
            firstField.focus();
        }
    }

    const tcc_resetButtons = document.querySelectorAll('button[type="reset"]');
    tcc_resetButtons.forEach(button => {
        button.addEventListener('click', () => {
            const parentForm = button.closest('form');
            const resultCard = parentForm.querySelector('.tcc-result-card');
            resultCard.innerHTML = '';
            tcc_setFirstFieldFocus(parentForm); 
            tcc_displayFirstMessage(parentForm);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.shiftKey && event.key === 'N') {
            const activeForm = document.querySelector('.tcc-tab-content.active form');
            const resetButton = activeForm.querySelector('button[type="reset"]');
            resetButton.click();
        }
    });

    const tcc_tabs = document.querySelectorAll('.tcc-tab');
    tcc_tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => {
            setTimeout(tcc_focusFirstField, 30); 
        });
    });

    tcc_focusFirstField();

    function tcc_calculateCost({ cbm, weight, method, goodsType, price }) {
        const rate = tcc_rates[method][goodsType];
        const volumetricWeight = cbm * 166.67; 
        
        let transportCharge = method === "air"
            ? weight * 1000 * rate
            : Math.max(weight / 1000, cbm) * rate;

        return transportCharge + tcc_clearance + tcc_fee * price;
    }

    function tcc_calculateContainerCost({ cbm, numberOfContainers, goodsType, containerType }) {
        const rate = tcc_rates.sea[goodsType];
        const totalCBM = cbm * numberOfContainers;
        const price = containerType === '20ft'
            ? tcc_price_per_20ft_container * numberOfContainers
            : tcc_price_per_40ft_container * numberOfContainers;

        return totalCBM * rate + tcc_clearance + tcc_fee * price;
    }

    tcc_boxesForm.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', () => {
            const length = parseFloat(document.getElementById('tcc-boxes-length').value) || 0;
            const width = parseFloat(document.getElementById('tcc-boxes-width').value) || 0;
            const height = parseFloat(document.getElementById('tcc-boxes-height').value) || 0;
            const weight = parseFloat(document.getElementById('tcc-boxes-weight').value) || 0;
            const numberOfBoxes = parseInt(document.getElementById('tcc-boxes-number').value) || 0;
            const method = document.getElementById('tcc-boxes-method').value;
            const goodsType = document.getElementById('tcc-boxes-goodsType').value;
            const price = parseFloat(document.getElementById('tcc-boxes-price').value) || 0;

            if (!length) return tcc_displayError(tcc_boxesResult, "لطفاً طول را وارد کنید.");
            if (!width) return tcc_displayError(tcc_boxesResult, "لطفاً عرض را وارد کنید.");
            if (!height) return tcc_displayError(tcc_boxesResult, "لطفاً ارتفاع را وارد کنید.");
            if (!weight) return tcc_displayError(tcc_boxesResult, "لطفاً وزن را وارد کنید.");
            if (!numberOfBoxes) return tcc_displayError(tcc_boxesResult, "لطفاً تعداد جعبه‌ها را وارد کنید.");
            if (!method) return tcc_displayError(tcc_boxesResult, "لطفاً روش حمل‌ونقل را انتخاب کنید.");
            if (!goodsType) return tcc_displayError(tcc_boxesResult, "لطفاً نوع کالا را انتخاب کنید.");
            if (!price) return tcc_displayError(tcc_boxesResult, "لطفاً قیمت را وارد کنید.");

            const cbmPerBox = (length * width * height) / 1000000;
            const totalCBM = cbmPerBox * numberOfBoxes;
            const totalWeight = weight * numberOfBoxes;
            const cost = tcc_calculateCost({ cbm: totalCBM, weight: totalWeight, method, goodsType, price });
            tcc_boxesResult.innerHTML = `<h5 class="tcc-text-success">هزینه حمل‌ونقل:</h5>
                <h3 class="tcc-text-danger" style="font-family: 'Arial', sans-serif;">¥ ${cost.toFixed(2)}</h3>`;
        });
    });

    tcc_containerForm.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', () => {
            const containerType = document.getElementById('tcc-container-type').value;
            const numberOfContainers = parseInt(document.getElementById('tcc-container-number').value) || 0;
            const goodsType = document.getElementById('tcc-container-goodsType').value;

            if (!containerType) return tcc_displayError(tcc_containerResult, "لطفاً نوع کانتینر را انتخاب کنید.");
            if (!numberOfContainers) return tcc_displayError(tcc_containerResult, "لطفاً تعداد کانتینرها را وارد کنید.");
            if (!goodsType) return tcc_displayError(tcc_containerResult, "لطفاً نوع کالا را انتخاب کنید.");

            const cbm = tcc_containerRates[containerType];
            const cost = tcc_calculateContainerCost({ cbm, numberOfContainers, goodsType, containerType  });
            tcc_containerResult.innerHTML = `<h5 class="tcc-text-success">هزینه حمل‌ونقل:</h5>
                <h3 class="tcc-text-danger" style="font-family: 'Arial', sans-serif;">¥ ${cost.toFixed(2)}</h3>`;
        });
    });

    tcc_focusFirstField();
});
