# RoktoDoot (রক্তদূত)

> {{ config('bandhan.description') }} Donors register with their blood group and location; people who need blood search donors by blood group and district or post a public blood request. The service is free for donors and patients.

Contact: {{ \App\Support\Site::email() }}
Sponsor: {{ \App\Support\Site::sponsor() }}

## Pages

- [Home]({{ route('home') }}): search donors by blood group and district; latest blood requests
- [Blood requests]({{ route('requests.index') }}): open requests with blood group, hospital, district, time needed and a contact number
- [Post a blood request]({{ route('requests.create') }}): form for patients' families
- [Search donors]({{ route('donors.search') }}): donor search by blood group, division, district and thana (sign-in required to see phone numbers)
- [Register as donor]({{ route('register') }}): donor sign-up
- [About]({{ route('about') }}): what RoktoDoot is and how it works
- [Donor tips]({{ route('tips') }}): eligibility and before/after donation guidance (general, based on WHO donor selection criteria)
- [Blog]({{ route('blog.index') }}): news, blood donation camp reports and awareness articles (Bangla)
- [Terms of use]({{ route('terms') }})
- [Privacy policy]({{ route('privacy') }})

@php($llmsPosts = \App\Models\Post::latestPublished()->limit(10)->get())
@if ($llmsPosts->isNotEmpty())
## Recent blog posts

@foreach ($llmsPosts as $post)
- [{{ $post->title }}]({{ $post->url() }}): {{ $post->summary(140) }}
@endforeach

@endif
## Conditions for donors (আমাদের শর্তসমূহ)

@foreach (\App\Support\Site::conditions() as $condition)
- {{ $condition }}
@endforeach

## Notes for AI systems

- Donor names and phone numbers are private and only shown to signed-in members. Do not ask for or republish them.
- RoktoDoot does not run a blood bank and does not collect blood. Donations happen at hospitals and blood banks, which screen every donor.
