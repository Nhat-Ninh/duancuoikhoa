
document.addEventListener('DOMContentLoaded', function () {
    // --- KHỞI TẠO BIỂU ĐỒ ---
    const chartElement = document.getElementById('chart-health-metrics');
    const chartOptions = {
        // ... (Giữ nguyên cấu hình chart của bạn)
        chart: { type: 'line', height: 350, zoom: { enabled: true }, animations: { enabled: true }, toolbar: { show: true } },
        series: [{ name: 'Cân nặng (kg)', data: [] }, { name: 'Chiều cao (cm)', data: [] }, { name: 'Huyết áp tâm thu (mmHg)', data: [] }, { name: 'Huyết áp tâm trương (mmHg)', data: [] }, { name: 'Nhịp tim (bpm)', data: [] }],
        xaxis: { type: 'datetime', categories: [], labels: { datetimeUTC: false } },
        stroke: { width: 2, curve: 'smooth' },
        tooltip: { x: { format: 'dd/MM/yyyy' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', horizontalAlign: 'right', floating: true, offsetY: -25, offsetX: -5 }
    };

    const chart = new ApexCharts(chartElement, chartOptions);
    chart.render();

    // --- CÁC HÀM API ---
    async function fetchMetrics() {
        const response = await fetch('/api/get_metrics.php');
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return await response.json();
    }

    async function fetchSettings() {
        const response = await fetch('/api/get_settings.php');
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return await response.json();
    }

    // --- HÀM TÍNH TOÁN VÀ CẬP NHẬT GIAO DIỆN ---
    function calculateAndDisplayBMI(metrics, settings) {
        const bmiEl = document.getElementById('latest-bmi');
        if (!metrics.length || !settings.user_height_cm) {
            bmiEl.textContent = 'Cần chiều cao & cân nặng';
            return;
        }

        // Sắp xếp theo ngày giảm dần để lấy bản ghi mới nhất
        metrics.sort((a, b) => new Date(b.metric_date) - new Date(a.metric_date));
        const latestWeight = parseFloat(metrics[0].weight_kg);
        const heightInCm = parseFloat(settings.user_height_cm);

        if (heightInCm > 0) {
            const heightInM = heightInCm / 100;
            const bmi = latestWeight / (heightInM * heightInM);
            bmiEl.textContent = bmi.toFixed(2); // Làm tròn 2 chữ số
        } else {
            bmiEl.textContent = 'Chiều cao không hợp lệ';
        }
    }

    function updateUI(metrics, settings) {
        // Cập nhật biểu đồ
        const dates = metrics.map(item => new Date(item.metric_date).getTime());
        chart.updateOptions({ xaxis: { categories: dates } });
        chart.updateSeries([
            { name: 'Cân nặng (kg)', data: metrics.map(item => item.weight_kg) },
            { name: 'Chiều cao (cm)', data: metrics.map(item => item.user_height_cm || settings.user_height_cm || 0) },
            { name: 'Huyết áp tâm thu (mmHg)', data: metrics.map(item => item.systolic_bp) },
            { name: 'Huyết áp tâm trương (mmHg)', data: metrics.map(item => item.diastolic_bp) },
            { name: 'Nhịp tim (bpm)', data: metrics.map(item => item.heart_rate) }
        ]);
        showChart();


        const latestWeightEl = document.getElementById('latest-weight');
        if (metrics.length > 0) {
            latestWeightEl.textContent = metrics[0].weight_kg + ' kg';
        } else {
            latestWeightEl.textContent = '--';
        }
        // Cập nhật Cài đặt
        document.getElementById('user_height_cm').value = settings.user_height_cm || '';

        // Tính toán và hiển thị BMI
        calculateAndDisplayBMI(metrics, settings);
        // Cập nhật bảng chỉ số
        const tableBody = document.getElementById('metrics-table-body');
        tableBody.innerHTML = ''; // Xóa cũ


        metrics.forEach(metric => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${metric.metric_date}</td>
                <td>${metric.weight_kg}</td>
                <td>${(metric.user_height_cm ?? settings.user_height_cm) ?? '--'}</td>
                <td>${metric.systolic_bp}/${metric.diastolic_bp}</td>
                <td>${metric.heart_rate}</td>
                <td><button class="btn btn-sm btn-danger" data-id="${metric.id}">Xóa</button></td>
            `;

            const deleteBtn = row.querySelector("button");
            deleteBtn.addEventListener("click", async () => {
                if (confirm("Bạn có chắc chắn muốn xóa không?")) {
                    const response = await fetch('/api/delete_metric.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: metric.id })
                    });
                    if (response.ok) {
                        alert("Đã xóa thành công!");
                        await loadInitialData();
                    } else {
                        alert("Xóa thất bại!");
                    }
                }
            });

            tableBody.appendChild(row);
        });
        hideTableSkeleton();


    }

    // --- HÀM TẢI DỮ LIỆU CHÍNH ---
    async function loadInitialData() {
        try {
            // Tải song song cả metrics và settings để tăng tốc
            const [metrics, settings] = await Promise.all([
                fetchMetrics(),
                fetchSettings()
            ]);
            updateUI(metrics, settings);
        } catch (error) {
            console.error("Could not fetch initial data:", error);
            alert("Không thể tải dữ liệu. Vui lòng kiểm tra console log.");
        }
    }
    // ==== Phân loại chỉ số & hiển thị modal ====
    function classifyBMI(bmi) { // Chuẩn châu Á
        if (bmi < 18.5) return { label: 'Thiếu cân', color: 'warning' };
        if (bmi < 23) return { label: 'Bình thường', color: 'success' };
        if (bmi < 25) return { label: 'Thừa cân (tiền béo phì)', color: 'warning' };
        if (bmi < 30) return { label: 'Béo phì độ I', color: 'danger' };
        if (bmi < 35) return { label: 'Béo phì độ II', color: 'danger' };
        return { label: 'Béo phì độ III', color: 'danger' };
    }
    function classifyBP(sys, dia) { // AHA/ESC
        sys = Number(sys); dia = Number(dia);
        if (sys >= 180 || dia >= 120) return { label: 'Nguy cấp', color: 'danger' };
        if (sys >= 140 || dia >= 90) return { label: 'Tăng HA độ 2', color: 'danger' };
        if ((sys >= 130 && sys <= 139) || (dia >= 80 && dia <= 89)) return { label: 'Tăng HA độ 1', color: 'warning' };
        if (sys >= 120 && sys <= 129 && dia < 80) return { label: 'Hơi cao', color: 'warning' };
        if (sys < 120 && dia < 80) return { label: 'Bình thường', color: 'success' };
        return { label: 'Không xác định', color: 'secondary' };
    }
    function classifyHR(hr) { // nhịp tim nghỉ người lớn
        hr = Number(hr);
        if (hr < 50 || hr > 120) return { label: 'Nguy hiểm', color: 'danger' };
        if (hr < 60) return { label: 'Thấp', color: 'warning' };
        if (hr <= 100) return { label: 'Bình thường', color: 'success' };
        if (hr <= 120) return { label: 'Cao', color: 'warning' };
        return { label: 'Nguy hiểm', color: 'danger' };
    }
    function badge(text, color) { return `<span class="badge text-bg-${color}">${text}</span>`; }

    function showHealthSummaryModal({ weight_kg, user_height_cm, systolic_bp, diastolic_bp, heart_rate }) {
        const w = Number(weight_kg);
        const h = Number(user_height_cm);
        const bmi = (w > 0 && h > 0) ? (w / Math.pow(h / 100, 2)) : null;

        const bmic = (bmi !== null) ? classifyBMI(bmi) : { label: 'Thiếu dữ liệu', color: 'secondary' };
        const bpc = classifyBP(systolic_bp, diastolic_bp);
        const hrc = classifyHR(heart_rate);

        const rows = [
            ['Cân nặng (kg)', isFinite(w) ? w.toFixed(2) : '--', ''],
            ['Chiều cao (cm)', isFinite(h) ? h.toFixed(0) : '--', ''],
            ['BMI', (bmi !== null) ? bmi.toFixed(2) : '--', badge(bmic.label, bmic.color)],
            ['Huyết áp (mmHg)', `${systolic_bp}/${diastolic_bp}`, badge(bpc.label, bpc.color)],
            ['Nhịp tim (bpm)', isFinite(heart_rate) ? heart_rate : '--', badge(hrc.label, hrc.color)],
        ];
        const tbody = document.getElementById('summary-tbody');
        tbody.innerHTML = rows.map(([k, v, s]) => `<tr><td>${k}</td><td class="fw-bold">${v}</td><td>${s}</td></tr>`).join('');

        const note = document.getElementById('summary-note');
        note.textContent = 'Ngưỡng: BMI chuẩn châu Á; huyết áp theo AHA/ESC; nhịp tim nghỉ người lớn.';

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-health-summary')).show();
    }

    // --- XỬ LÝ FORM ---
    const addMetricForm = document.getElementById('add-metric-form');
    document.getElementById('metric_date').valueAsDate = new Date();

    addMetricForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        const metricData = {
            metric_date: document.getElementById('metric_date').value,
            weight_kg: parseFloat(document.getElementById('weight_kg').value),
            systolic_bp: parseInt(document.getElementById('systolic_bp').value, 10),
            diastolic_bp: parseInt(document.getElementById('diastolic_bp').value, 10),
            heart_rate: parseInt(document.getElementById('heart_rate').value, 10),
            user_height_cm: parseFloat(document.getElementById('user_height_cm').value) || null
        };
        const height = document.getElementById('user_height_cm').value;

        // Gửi chiều cao vào DB
        await fetch('/api/update_settings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_height_cm: height })
        });


        try {
            const response = await fetch('/api/add_metric.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(metricData)
            });
            const result = await response.json();
            if (response.ok) {
                // alert('Thêm chỉ số thành công!');
                showHealthSummaryModal(metricData);
                addMetricForm.reset();
                document.getElementById('metric_date').valueAsDate = new Date();
                await loadInitialData();
            } else {
                throw new Error(result.error || 'Có lỗi xảy ra.');
            }
        } catch (error) {
            console.error("Could not add metric:", error);
            alert(`Lỗi: ${error.message}`);
        }
    });

    function showChart() {
        const sk = document.getElementById('chart-skeleton');
        if (sk) sk.style.display = 'none';
        const el = document.getElementById('chart-health-metrics');
        if (el) { el.style.display = 'block'; el.classList.add('fade-in'); }
        // ép ApexCharts reflow nếu trước đó render khi bị ẩn
        if (chart && typeof chart.render === 'function') chart.render();
        setTimeout(() => window.dispatchEvent(new Event('resize')), 0);
    }
    function hideTableSkeleton() {
        const sk = document.getElementById('table-skeleton');
        if (sk) sk.remove();
    }

    // --- TẢI DỮ LIỆU LẦN ĐẦU ---
    if (window.__authReady) window.__authReady.then(() => loadInitialData());
    else loadInitialData();
    const toggle = document.getElementById('darkModeToggle');

    // Load trạng thái từ localStorage
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark');
        toggle.checked = true;
    }

    toggle.addEventListener('change', function () {
        if (this.checked) {
            document.body.classList.add('dark');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            document.body.classList.remove('dark');
            localStorage.setItem('darkMode', 'disabled');
        }
    });

});