<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />


        <form id="registrationForm" method="POST" action="{{ route('register') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Man Number -->
            <flux:input
                name="man_no"
                :label="__('Man Number')"
                :value="old('man_no')"
                type="text"
                required
                autofocus
                :placeholder="__('Enter your Man Number')"
                id="man_no"
            />

            <!-- Work Email -->
            <flux:input
                name="email"
                :label="__('Work Email')"
                :value="old('email')"
                type="email"
                required
                :placeholder="__('username@zesco.co.zm')"
                id="email"
            />

            <!-- Extra Fields (hidden initially) -->
            <div id="extra-fields" class="flex flex-col gap-6" style="display:none;">
                <flux:input
                    name="name"
                    :label="__('Full Name')"
                    type="text"
                    readonly
                    id="emp_name"
                />
                <flux:input
                    name="directorate"
                    :label="__('Directorate')"
                    type="text"
                    readonly
                    id="emp_directorate"
                />
                <flux:input
                    name="job_title"
                    :label="__('Job Title')"
                    type="text"
                    readonly
                    id="emp_job_title"
                />

                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    viewable
                    id="password"
                />
                <div class="progress mt-2" style="height:5px;">
                    <div id="passwordStrength" class="progress-bar"></div>
                </div>

                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm Password')"
                    type="password"
                    required
                    viewable
                    id="confirm_password"
                />
                <small id="matchLabel" class="fw-semibold"></small>
            </div>
            <div id="messageBox" class="text-center text-sm mb-2"></div>

            <flux:button type="button" variant="primary" class="w-full" id="mainBtn">
    <span id="btnContent">
        <i class="bi bi-search"></i>
        <span id="btnText">{{ __('Verify Employee') }}</span>
    </span>

                <span id="btnLoader" style="display:none;">
        <span class="spinner-border spinner-border-sm"></span>
        Verifying...
    </span>
            </flux:button>

        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            let isVerified = false;
            const mainBtn = $('#mainBtn');
            const btnText = $('#btnText');
            const btnIcon = $('#btnIcon');
            const extraFields = $('#extra-fields');

            // Reset logic if man_no or email changes
            $('#man_no, #email').on('input', function() {
                if (isVerified) {
                    isVerified = false;
                    extraFields.slideUp();
                    mainBtn.removeClass('btn-success').addClass('btn-primary');
                    btnText.text('Verify Employee');
                    btnIcon.removeClass('bi-check-circle').addClass('bi-search');
                    $('#password, #confirm_password').val('');
                    $('#passwordStrength').css('width','0%');
                    $('#matchLabel').text('');
                }
            });

            mainBtn.click(function() {

                if(isVerified){
                    $('#registrationForm').submit();
                    return;
                }

                let man_no = $('#man_no').val().trim();
                let email = $('#email').val().trim();

                if(!man_no || !email){
                    showMessage('Please enter Man Number and Email.', 'danger');
                    return;
                }

                // 🔹 Show loader
                mainBtn.prop('disabled', true);
                $('#btnContent').hide();
                $('#btnLoader').show();
                showMessage('', '');

                $.ajax({
                    url: "{{ route('register.verify') }}",
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'},
                    data: {man_no: man_no, email: email},

                    success: function(res){
                        if(res.success){

                            $('#emp_name').val(res.user.name);
                            $('#emp_directorate').val(res.user.directorate);
                            $('#emp_job_title').val(res.user.job_title);

                            extraFields.slideDown();

                            mainBtn.removeClass('btn-primary').addClass('btn-success');
                            $('#btnText').text('Submit Registration');

                            showMessage('Employee verified successfully ✅', 'success');

                            isVerified = true;

                        } else {
                            showMessage(res.message || 'Verification failed', 'danger');
                        }
                    },

                    error:function(xhr){
                        let msg = 'System error. Try again.';
                        if(xhr.responseJSON && xhr.responseJSON.message){
                            msg = xhr.responseJSON.message;
                        }
                        showMessage(msg, 'danger');
                    },

                    complete:function(){
                        mainBtn.prop('disabled', false);
                        $('#btnLoader').hide();
                        $('#btnContent').show();
                    }
                });
            });


            // Password strength & match
            $('#password, #confirm_password').on('input', function(){
                let pwd = $('#password').val();
                let conf = $('#confirm_password').val();
                let score = 0;
                if(pwd.length>=8) score+=20;
                if(/[A-Z]/.test(pwd)) score+=20;
                if(/[0-9]/.test(pwd)) score+=20;
                if(/[^A-Za-z0-9]/.test(pwd)) score+=40;

                $('#passwordStrength').css('width',score+'%')
                    .toggleClass('bg-danger',score<50)
                    .toggleClass('bg-success',score>=80);

                if(conf!==""){
                    if(pwd===conf) $('#matchLabel').text('Match ✅').css('color','green');
                    else $('#matchLabel').text('No match ❌').css('color','red');
                }
            });
        });

        function showMessage(message, type){
            const box = $('#messageBox');

            if(!message){
                box.html('');
                return;
            }

            let color = type === 'success' ? 'green' : 'red';

            box.html(`<span style="color:${color}; font-weight:600;">${message}</span>`);
        }

    </script>
</x-layouts::auth>
