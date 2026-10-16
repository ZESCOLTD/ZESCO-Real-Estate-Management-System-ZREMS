<div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Payments</span>


            <button
               class="btn btn-sm text-white bg-warning mr-2"
               data-toggle="modal"
               data-target="#paymentModal">
                <i class="fas fa-plus mr-1"></i>
                New Payment
            </button>
        </div>

        <div class="card-body table-responsive">

            <table class="table table-sm table-bordered">
                <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Payee</th>
                    <th class="text-end">Amount</th>
                    <th>Mode</th>
                    <th>Reference</th>
                    <th>Status</th>
                </tr>
                </thead>

                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date }}</td>
                        <td>{{ $payment->payment_type }}</td>
                        <td>{{ $payment->payee }}</td>
                        <td class="text-end">{{ number_format($payment->amount,2) }}</td>
                        <td>{{ $payment->payment_mode }}</td>
                        <td>{{ $payment->reference_no }}</td>
                        <td>
                            @if($payment->cet_board_approval)
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No payments found
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>

        </div>
    </div>

    {{-- MODAL --}}
    @include('livewire.fms-assets-register.modals.payment-create')

</div>
