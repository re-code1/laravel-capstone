@extends('layouts.app')

@section('title', $section->name)

@section('content')
    <h1>{{ $section->name }}</h1>

    <table>
        <thead>
            <tr>
                <th>LRN</th>
                <th>Student</th>
                <th>General average</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summaries as $summary)
                <tr>
                    <td>{{ $summary['student']->lrn }}</td>
                    <td>
                        <a href="{{ url('/students/' . $summary['student']->id) }}">{{ $summary['student']->fullName() }}</a>
                    </td>
                    <td>{{ $summary['average'] ?? '-' }}</td>
                    <td class="{{ strtolower(str_replace(' ', '-', $summary['remark'])) }}">{{ $summary['remark'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><a href="{{ url('/sections') }}">Back to sections</a></p>
@endsection
