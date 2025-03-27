<h2>Hello {{ $user->name }},</h2>

@if ($type === 'new_case')
    <p>A new case ({{ $case->case_name }}) has been reported in your area ({{ $case->city }}).</p>
    <p>Description: {{ $case->description }}</p>
@elseif ($type === 'case_update')
    <p>The case "{{ $case->case_name }}" has been updated.</p>
    <p>New Status: {{ $case->status ?? 'Check for more details' }}</p>
@elseif ($type === 'alert')
    <p>{{ $customMessage }}</p>
@endif

<p>Stay safe,<br>District Core Authority</p>
