@extends('layouts/AdminblankLayout')

@section('title', 'Monthly Stats')

@section('page-script')
    <link href="https://cdn.datatables.net/v/dt/dt-2.1.8/datatables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.1.8/datatables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare month dropdown
            const recentSubscriptions = @json($recentSubscriptions);
            const months = [...new Set(recentSubscriptions.map(sub => (new Date(sub.created_at)).toLocaleString('default', { month: 'short', year: 'numeric' })))];
            // Sort months descending (most recent first)
            months.sort((a, b) => {
                const parse = m => new Date('1 ' + m);
                return parse(b) - parse(a);
            });
            let selectedMonth = months[0];

            function renderStats(month) {
                // Group by membership plan
                const plans = {};
                recentSubscriptions.forEach(sub => {
                    const subMonth = (new Date(sub.created_at)).toLocaleString('default', { month: 'short', year: 'numeric' });
                    if (subMonth !== month) return;
                    const planId = sub.membership_plan.id;
                    if (!plans[planId]) {
                        plans[planId] = {
                            plan: sub.membership_plan,
                            members: [],
                            revenue: 0
                        };
                    }
                    plans[planId].members.push(sub.member);
                });
                // After grouping, calculate revenue as price * number of members
                Object.values(plans).forEach(planData => {
                    planData.revenue = planData.plan.price * planData.members.length;
                });
                // Render cards
                const container = document.getElementById('plan-cards');
                container.innerHTML = '';
                if (Object.keys(plans).length === 0) {
                    container.innerHTML = '<div class="alert alert-info">No subscriptions for this month.</div>';
                    return;
                }
                Object.values(plans).forEach((planData, idx) => {
                    const cardId = `planCard${planData.plan.id}`;
                    const collapseId = `collapse${planData.plan.id}`;
                    const membersTableId = `membersTable${planData.plan.id}`;
                    const card = document.createElement('div');
                    card.className = 'card mb-3 shadow-sm';
                    card.innerHTML = `
                        <div class="card-header d-flex justify-content-between align-items-center" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-primary fs-5">${planData.plan.name}</span>
                                </div>
                                <div>
                                    <div><strong>Price:</strong> <span class="text-success">${planData.plan.price} ETB</span></div>
                                    <div><strong>Allowed Entries:</strong> <span class="text-info">${planData.plan.allowed_entries == null ? 'Unlimited' : planData.plan.allowed_entries}</span></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="fs-4 fw-bold text-success">${planData.revenue} ETB</span><br>
                                <small class="text-muted">Total Revenue</small>
                            </div>
                        </div>
                        <div id="${collapseId}" class="collapse" data-bs-parent="#plan-cards">
                            <div class="card-body">
                                <h6 class="mb-3">Members Subscribed</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="${membersTableId}">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${planData.members.map((member, i) => `
                                                <tr>
                                                    <td>${i + 1}</td>
                                                    <td>${member ? member.firstname + ' ' + member.lastname : '<span class=\'text-danger\'>N/A</span>'}</td>
                                                    <td>${member ? member.email : '<span class=\'text-danger\'>N/A</span>'}</td>
                                                    <td>${member ? member.phone_number : '<span class=\'text-danger\'>N/A</span>'}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(card);
                    setTimeout(() => {
                        if (window.jQuery && window.jQuery.fn.DataTable) {
                            $(`#${membersTableId}`).DataTable({
                                paging: false,
                                searching: false,
                                info: false,
                                ordering: true,
                                responsive: true
                            });
                        }
                    }, 100);
                });
            }

            // Populate dropdown
            const monthSelect = document.getElementById('monthSelect');
            monthSelect.innerHTML = months.map(m => `<option value="${m}">${m}</option>`).join('');
            monthSelect.value = selectedMonth;
            monthSelect.addEventListener('change', function() {
                selectedMonth = this.value;
                renderStats(selectedMonth);
            });
            renderStats(selectedMonth);
        });
    </script>
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">
            <div class="col-md-6">
                <h4 class="fw-bold">Subscription Stats (Last 3 Months)</h4>
            </div>
            <div class="col-md-6 text-end">
                <label for="monthSelect" class="form-label fw-bold me-2">Select Month:</label>
                <select id="monthSelect" class="form-select d-inline-block w-auto" style="min-width: 120px;"></select>
            </div>
        </div>
        <div id="plan-cards"></div>
    </div>
@endsection
