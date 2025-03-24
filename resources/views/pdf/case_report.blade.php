<h1>Case Report: {{ $case->case_name }}</h1>
<p><strong>Case Number:</strong> {{ $case->case_number }}</p>
<p><strong>Description:</strong> {{ $case->description }}</p>
<p><strong>Area:</strong> {{ $case->area }} | <strong>City:</strong> {{ $case->city }}</p>
<p><strong>Created By:</strong> {{ $case->createdBy->name }} on {{ $case->created_at->format('Y-m-d') }}</p>

<h2>Evidence</h2>
@foreach($evidence as $item)
    <p>Type: {{ $item->type }}</p>
    @if($item->type === 'text')
        <p>{{ $item->description }}</p>
    @else
        <img src="{{ public_path('storage/' . $item->file_path) }}" style="width:200px;"><br>
    @endif
@endforeach

<h2>Suspects</h2>
@foreach($suspects as $suspect)
    <p>{{ $suspect->name }}, Age: {{ $suspect->age }}, Role: {{ $suspect->role }}</p>
@endforeach

<h2>Victims</h2>
@foreach($victims as $victim)
    <p>{{ $victim->name }}, Age: {{ $victim->age }}, Role: {{ $victim->role }}</p>
@endforeach

<h2>Witnesses</h2>
@foreach($witnesses as $witness)
    <p>{{ $witness->name }}, Age: {{ $witness->age }}, Role: {{ $witness->role }}</p>
@endforeach
