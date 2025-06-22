<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Monitor</title>
    <link rel="stylesheet" href="{{ asset('admin/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        body {
            background-color: #f4f6f9;
            padding: 20px;
        }
        .call-status-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
        }
        .call-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .call-info {
            margin-bottom: 20px;
        }
        .call-timer {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="call-status-container">
        <div class="call-icon">
            <i class="fas fa-phone"></i>
        </div>
        <h3>Call in Progress</h3>
        
        <div class="call-info">
            <p>Call ID: <span id="callId">{{ request('call_id') ?? 'Unknown' }}</span></p>
        </div>
        
        <div class="call-timer" id="callTimer">00:00</div>
        
        <div class="btn-group">
            <button type="button" class="btn btn-danger" id="endCallBtn">
                <i class="fas fa-phone-slash"></i> End Call
            </button>
        </div>
    </div>

    <script>
        // Call timer functionality
        let seconds = 0;
        const timerElement = document.getElementById('callTimer');
        
        const timer = setInterval(() => {
            seconds++;
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        }, 1000);
        
        // End call button
        document.getElementById('endCallBtn').addEventListener('click', function() {
            clearInterval(timer);
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ending...';
            
            const callId = document.getElementById('callId').textContent;
            
            // Here you would add your API call to end the call
            // For now, we'll just close the window after a delay
            setTimeout(() => {
                window.close();
            }, 1500);
        });
    </script>
</body>
</html> 