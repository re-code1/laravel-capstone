@extends('layouts.app')

@section('title', $student->fullName())

@section('content')
    <h1>{{ $student->fullName() }}</h1>

    <p>
        LRN: {{ $student->lrn }}<br>
        Grade {{ $student->section->grade_level }} - {{ $student->section->name }}<br>
        Adviser: {{ $student->section->adviser?->fullName() ?? 'none' }}
    </p>

    @if ($rows->isEmpty())
        <p>No grades recorded yet.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Sem 1</th>
                    <th>Sem 2</th>
                    <th>Final</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['subject']->name }}</td>
                        <td>{{ $row['sem1'] }}</td>
                        <td>{{ $row['sem2'] }}</td>
                        <td>{{ $row['final'] ?? '-' }}</td>
                        <td class="{{ strtolower(str_replace(' ', '-', $row['remark'])) }}">{{ $row['remark'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="summary">
        General average: {{ $average ?? '-' }}<br>
        Remark: <span class="{{ strtolower(str_replace(' ', '-', $remark)) }}">{{ $remark }}</span>
    </p>

    <p><a href="{{ url('/sections/' . $student->section_id) }}">Back to {{ $student->section->name }}</a></p>
@endsection
