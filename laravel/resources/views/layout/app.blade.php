<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Category App')</title>
    
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col items-center pt-10 px-4 pb-12">

    <header class="w-full max-w-5xl text-center mb-6">
        <h1 class="text-4xl md:text-5xl font-bold text-slate-800 tracking-tight">
            @yield('page_title')
        </h1>
    </header>

    <main class="w-full max-w-5xl bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
        @yield('content')
    </main>

    <script>
        async function handleApiFormSubmit(event) {
            event.preventDefault();

            const form = event.currentTarget;
            const submitButton = form.querySelector('[type="submit"]');
            const originalText = submitButton ? submitButton.textContent : '';

            form.querySelectorAll('[data-error-for]').forEach((element) => {
                element.textContent = '';
            });

            form.querySelectorAll('input, textarea, select').forEach((element) => {
                element.classList.remove('border-red-500', 'ring-1', 'ring-red-100');
            });

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
            }

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }

                    if (data.errors) {
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            const control = form.querySelector(`[name="${field}"]`);
                            const error = form.querySelector(`[data-error-for="${field}"]`);

                            if (control) {
                                control.classList.remove('border-gray-300');
                                control.classList.add('border-red-500', 'ring-1', 'ring-red-100');
                            }

                            if (error) {
                                error.classList.remove('hidden');
                                error.textContent = Array.isArray(messages) ? messages[0] : messages;
                            }
                        });
                    }

                    if (data.message) {
                        alert(data.message);
                    }

                    return;
                }

                window.location.href = form.dataset.redirect || '/categories';
            } catch (error) {
                alert('Something went wrong. Please try again.');

                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            }
        }

        document.querySelectorAll('[data-api-form]').forEach((form) => {
            form.querySelectorAll('input, textarea, select').forEach((element) => {
                element.addEventListener('input', () => {
                    element.classList.remove('border-red-500', 'ring-1', 'ring-red-100');
                    element.classList.add('border-gray-300');
                    const error = form.querySelector(`[data-error-for="${element.name}"]`);
                    if (error) {
                        error.classList.add('hidden');
                        error.textContent = '';
                    }
                });
            });

            form.addEventListener('submit', handleApiFormSubmit);
        });
    </script>

</body>
</html>