<x-mail::message>
# Hello, {{ $travelOrder->applicant_name }}!

Your travel order status has been updated to {{ $travelOrder->status }}.

<x-mail::button :url="''">
    More details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>