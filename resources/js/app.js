const wizard = (() => {
    const steps = [
        { id: 'requirements', label: 'Server Requirements', desc: 'Verify your server meets all requirements.' },
        { id: 'database', label: 'Database Setup', desc: 'Configure your database connection.' },
        { id: 'environment', label: 'Environment', desc: 'Set up your application environment.' },
        { id: 'installation', label: 'Installation', desc: 'Run migrations and install dependencies.' },
        { id: 'complete', label: 'Complete', desc: 'Your application is ready to use.' },
    ];

    let currentIndex = 0;
    let completedSet = new Set();

    const stepsNav = document.getElementById('steps-nav');
    const stepContent = document.getElementById('step-content');

    const iconPass = `<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>`;
    const iconFail = `<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>`;
    const iconWarn = `<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>`;

    const renderers = {
        requirements() {
            const items = window.__serverRequirements || [];

            const renderItem = (item) => {
                let statusClass = 'pass';
                let icon = iconPass;
                if (!item.status) {
                    statusClass = item.required ? 'fail' : 'warn';
                    icon = item.required ? iconFail : iconWarn;
                }
                const optional = !item.required ? `<span class="req-optional">Optional</span>` : '';
                const detail = item.detail ? `<span class="req-detail">${item.detail}</span>` : '';
                return `<div class="req-item"><div class="req-label">${item.label}${detail}${optional}</div><span class="req-status ${statusClass}">${icon}</span></div>`;
            };

            return `<div class="req-list">${items.map(renderItem).join('')}</div>`;
        },
    };

    function canProceed() {
        const step = steps[currentIndex];
        if (step.id === 'requirements') {
            const items = window.__serverRequirements || [];
            return items.filter(item => item.required).every(item => item.status);
        }
        return true;
    }

    function renderNav() {
        stepsNav.innerHTML = steps.map((step, index) => {
            const isActive = index === currentIndex;
            const isCompleted = completedSet.has(index);
            const isReachable = isCompleted || index < currentIndex;

            let classes = 'step-item';
            if (isActive) classes += ' active';
            if (isCompleted) classes += ' completed';
            if (isReachable && !isActive) classes += ' reachable';

            return `<div class="${classes}" data-index="${index}"><span class="step-label">${step.label}</span></div>`;
        }).join('');

        stepsNav.querySelectorAll('.step-item.reachable').forEach(el => {
            el.addEventListener('click', () => goTo(parseInt(el.dataset.index)));
        });
    }

    function renderContent() {
        const step = steps[currentIndex];
        const isFirst = currentIndex === 0;
        const isLast = currentIndex === steps.length - 1;
        const proceed = canProceed();

        const backBtn = !isFirst ? `<button class="btn btn-secondary" id="btn-back">Back</button>` : '';
        const nextBtn = !isLast ? `<button class="btn btn-primary" id="btn-next" ${!proceed ? 'disabled' : ''}>Continue</button>` : '';
        const finishBtn = isLast ? `<button class="btn btn-primary" id="btn-finish">Finish setup</button>` : '';

        stepContent.innerHTML = `<div class="step-panel">
<div class="panel-header">
<span class="step-eyebrow">Step ${currentIndex + 1} of ${steps.length}</span>
<h2 class="panel-title">${step.label}</h2>
<p class="panel-desc">${step.desc}</p>
</div>
<div class="panel-body" id="panel-body"></div>
<div class="panel-footer">
<div class="step-actions"><div>${backBtn}</div><div>${nextBtn}${finishBtn}</div></div>
</div>
</div>`;

        if (renderers[step.id]) {
            document.getElementById('panel-body').innerHTML = renderers[step.id]();
        }

        document.getElementById('btn-next')?.addEventListener('click', next);
        document.getElementById('btn-back')?.addEventListener('click', back);
        document.getElementById('btn-finish')?.addEventListener('click', finish);
    }

    function goTo(index) {
        currentIndex = index;
        renderNav();
        renderContent();
    }

    function next() {
        if (!canProceed()) return;
        completedSet.add(currentIndex);
        if (currentIndex < steps.length - 1) goTo(currentIndex + 1);
    }

    function back() {
        if (currentIndex > 0) goTo(currentIndex - 1);
    }

    function finish() {
        completedSet.add(currentIndex);
        renderNav();
    }

    function init() {
        renderNav();
        renderContent();
    }

    return { init, goTo, next, back };
})();

document.addEventListener('DOMContentLoaded', () => wizard.init());
