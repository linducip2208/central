@php
$map = [
'DRAFT' => 'bg-secondary-lt', 'SUBMITTED' => 'bg-blue-lt', 'APPROVED' => 'bg-green-lt',
'REJECTED' => 'bg-red-lt', 'ORDERED' => 'bg-indigo-lt', 'PARTIAL' => 'bg-yellow-lt',
'COMPLETED' => 'bg-green-lt', 'CANCELLED' => 'bg-dark-lt', 'RECEIVED' => 'bg-green-lt',
'PLANNED' => 'bg-secondary-lt', 'RELEASED' => 'bg-blue-lt', 'IN_PROGRESS' => 'bg-yellow-lt',
'PENDING' => 'bg-yellow-lt', 'PASSED' => 'bg-green-lt', 'FAILED' => 'bg-red-lt',
'CONDITIONAL' => 'bg-orange-lt', 'PACKED' => 'bg-blue-lt', 'IN_TRANSIT' => 'bg-indigo-lt',
'DELIVERED' => 'bg-green-lt', 'RETURNED' => 'bg-orange-lt', 'COUNTED' => 'bg-blue-lt',
'POSTED' => 'bg-green-lt', 'ACTIVE' => 'bg-green-lt', 'INACTIVE' => 'bg-secondary-lt',
'AVAILABLE' => 'bg-green-lt', 'BLOCKED' => 'bg-yellow-lt', 'EXPIRED' => 'bg-red-lt', 'DEPLETED' => 'bg-dark-lt',
];
$class = $map[$status] ?? 'bg-secondary-lt';
@endphp
<span class="badge {{ $class }}">{{ $status }}</span>
