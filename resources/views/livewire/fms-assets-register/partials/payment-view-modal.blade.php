<div class="modal fade" id="viewPaymentModal{{ $payment->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <p><strong>Type:</strong> {{ $payment->payment_type }}</p>
                <p><strong>Payee:</strong> {{ $payment->payee }}</p>
                <p><strong>Amount:</strong> {{ number_format($payment->amount, 2) }}</p>
                <p><strong>Mode:</strong> {{ $payment->payment_mode }}</p>
                <p><strong>Reference:</strong> {{ $payment->reference_no }}</p>
                <p><strong>Date:</strong> {{ $payment->payment_date }}</p>

                <p><strong>Board Approval:</strong>
                    {{ $payment->cet_board_approval ? 'Yes' : 'No' }}
                </p>

                @if($payment->attachments)
                    <hr>
                    <strong>Attachments:</strong>
                    <ul>
                        @foreach($payment->attachments as $file)
                            <li>
                                <a href="{{ Storage::url($file) }}" target="_blank">
                                    View File
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

            </div>

        </div>
    </div>
</div>
