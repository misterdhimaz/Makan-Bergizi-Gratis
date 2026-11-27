@props(['title' => null, 'pageTitle' => null, 'pageSubtitle' => null])

@extends('layouts.app')

@if($title)
    @section('title', $title)
@endif

@if($pageTitle)
    @section('page_title', $pageTitle)
@endif

@if($pageSubtitle)
    @section('page_subtitle', $pageSubtitle)
@endif

@section('content')
    {{ $slot }}
@endsection
