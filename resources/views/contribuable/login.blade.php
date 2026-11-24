<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portail Contribuable - SICOFIL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2.5rem;
            max-width: 450px;
            width: 100%;
        }
        .logo-contribuable {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo-contribuable img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #667eea;
        }
        .logo-contribuable h3 {
            color: #667eea;
            font-weight: bold;
            margin-top: 1rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #e1e8ed;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        #otp-section {
            display: none;
        }
        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 5px;
            border-radius: 10px;
            border: 2px solid #e1e8ed;
        }
        .otp-input:focus {
            border-color: #667eea;
            outline: none;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-contribuable">
            <img src="{{ asset('images/logos/snd.jpeg') }}" alt="SICOFIL">
            <h3>Portail Contribuable</h3>
            <p class="text-muted">SICOFIL - Mairie de {{ session('commune_nom', 'Sandiara') }}</p>
        </div>

        <!-- Section Téléphone -->
        <div id="phone-section">
            <form id="phone-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-phone"></i> Numéro de téléphone</label>
                    <input type="tel" name="telephone" id="telephone" class="form-control" 
                           placeholder="77 123 45 67" required>
                    <small class="text-muted">Format: 77/78/76/70/75 suivi de 7 chiffres</small>
                </div>
                <button type="submit" class="btn btn-primary w-100" id="send-otp-btn">
                    <i class="fas fa-paper-plane"></i> Recevoir le code
                </button>
            </form>
        </div>

        <!-- Section OTP -->
        <div id="otp-section">
            <p class="text-center text-muted mb-3">
                Code envoyé au <strong id="phone-display"></strong>
            </p>
            <form id="otp-form">
                @csrf
                <input type="hidden" name="telephone" id="telephone-hidden">
                <div class="d-flex justify-content-center mb-3">
                    <input type="text" maxlength="1" class="otp-input" data-index="0">
                    <input type="text" maxlength="1" class="otp-input" data-index="1">
                    <input type="text" maxlength="1" class="otp-input" data-index="2">
                    <input type="text" maxlength="1" class="otp-input" data-index="3">
                    <input type="text" maxlength="1" class="otp-input" data-index="4">
                    <input type="text" maxlength="1" class="otp-input" data-index="5">
                </div>
                <input type="hidden" name="otp" id="otp-value">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
                <button type="button" class="btn btn-link w-100 mt-2" id="resend-otp">
                    Renvoyer le code
                </button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('welcome') }}" class="text-muted">
                <i class="fas fa-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = "{{ url('/') }}";

        // Envoi du téléphone
        document.getElementById('phone-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('send-otp-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';

            const telephone = document.getElementById('telephone').value;

            fetch(BASE_URL + '/contribuable/send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ telephone })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('phone-section').style.display = 'none';
                    document.getElementById('otp-section').style.display = 'block';
                    document.getElementById('phone-display').textContent = telephone;
                    document.getElementById('telephone-hidden').value = telephone;
                    if (data.otp_dev) {
                        document.getElementById('phone-display').innerHTML = telephone + ' <span class="badge bg-warning text-dark">Code dev: ' + data.otp_dev + '</span>';
                    }
                    document.querySelector('.otp-input').focus();
                } else {
                    alert(data.message || 'Erreur lors de l\'envoi du code');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Recevoir le code';
                }
            })
            .catch(err => {
                alert('Erreur réseau. Vérifiez votre connexion.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Recevoir le code';
            });
        });

        // Gestion des inputs OTP
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                // Mise à jour du code complet
                updateOtpValue();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });

        function updateOtpValue() {
            const code = Array.from(otpInputs).map(i => i.value).join('');
            document.getElementById('otp-value').value = code;
        }

        // Soumission OTP
        document.getElementById('otp-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const otp = document.getElementById('otp-value').value;
            const telephone = document.getElementById('telephone-hidden').value;

            if (otp.length !== 6) {
                alert('Veuillez saisir le code à 6 chiffres');
                return;
            }

            fetch(BASE_URL + '/contribuable/verify-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ telephone, otp })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || (BASE_URL + '/contribuable/dashboard');
                } else {
                    alert(data.message || 'Code incorrect');
                    otpInputs.forEach(i => i.value = '');
                    otpInputs[0].focus();
                }
            });
        });

        // Renvoyer le code
        document.getElementById('resend-otp').addEventListener('click', function() {
            document.getElementById('phone-section').style.display = 'block';
            document.getElementById('otp-section').style.display = 'none';
        });
    </script>
</body>
</html>
