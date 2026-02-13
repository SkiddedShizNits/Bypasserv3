<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Instance - Age Bypasser</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #02040a 0%, #0a0e27 50%, #02040a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .glass-effect {
            backdrop-filter: blur(10px) saturate(180%);
            background-color: rgba(17, 25, 40, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
    </style>
</head>
<body class="bg-[#02040a] text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="glass-effect rounded-3xl p-8 shadow-2xl">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 glass-effect">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                </div>
                <h1 class="text-3xl font-bold mb-2">Create Instance</h1>
                <p class="text-white/60">Generate your bypasser instance</p>
            </div>
            
            <form id="createForm" class="space-y-6">
                <div>
                    <label class="text-sm font-medium text-white/80 ml-1 block mb-2">Directory Name</label>
                    <input 
                        type="text" 
                        name="dir" 
                        placeholder="myinstance" 
                        class="w-full bg-white/5 border border-white/10 focus:border-white/20 text-white rounded-2xl px-4 py-3 outline-none transition-colors glass-effect"
                        pattern="[A-Za-z0-9]+"
                        title="Only letters and numbers allowed"
                        required
                    >
                </div>
                
                <div>
                    <label class="text-sm font-medium text-white/80 ml-1 block mb-2">Discord Webhook URL</label>
                    <input 
                        type="url" 
                        name="web" 
                        placeholder="https://discord.com/api/webhooks/..." 
                        class="w-full bg-white/5 border border-white/10 focus:border-white/20 text-white rounded-2xl px-4 py-3 outline-none transition-colors glass-effect"
                        required
                    >
                </div>
                
                <input type="hidden" name="t" value="ab">
                
                <button type="submit" class="w-full h-14 bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white rounded-2xl text-base font-bold transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                    Create Instance
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="/" class="text-sm text-white/60 hover:text-white transition">← Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('createForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);
            
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating...';
            
            try {
                const params = new URLSearchParams(formData);
                const response = await fetch(`/dashboard/apis/create.php?${params}`);
                const text = await response.text();
                
                if (text === '') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Instance created! Check your Discord webhook for details.',
                        confirmButtonText: 'Go to Dashboard',
                        background: '#0a0e27',
                        color: '#f8fafc'
                    });
                    window.location.href = '/dashboard/sign-in.php';
                } else {
                    throw new Error(text);
                }
            } catch (error) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to create instance',
                    background: '#0a0e27',
                    color: '#f8fafc'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg> Create Instance';
            }
        });
    </script>
</body>
</html>
