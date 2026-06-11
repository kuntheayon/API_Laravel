<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @yield('page_title', 'Category App')
    
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

</body>
</html>