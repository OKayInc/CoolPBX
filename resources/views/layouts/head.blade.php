<title>{{ $title ?? config('app.name', 'Backoffice Dashboard') }} | {{ config('app.name', 'Admin Panel') }}</title>
<meta name="description" content="{{ $description ?? 'Secure administrative panel for managing application resources and settings.' }}">

<meta name="author" content="{{ $author ?? config('app.author', 'Your Company Name') }}">
<meta name="keywords" content="{{ $keywords ?? 'admin, dashboard, backoffice, management, panel' }}">
<meta name="api-token" content="{{ auth()->user()->api_key ?? '' }}">
<meta name="csrf-token" content="{{ csrf_token() }}">