@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Operasional SPPG Sungai Selayur 1')

@push('styles')
<style>
    body:has(.dashboard-page) header input[x-ref="globalSearchInput"] {
        height: 40px !important;
        min-height: 40px !important;
        border-color: #D8E1EC;
        background-color: #F8FAFC;
        color: #0F172A;
        transition: border-color 160ms ease, background-color 160ms ease, box-shadow 160ms ease;
    }

    body:has(.dashboard-page) header input[x-ref="globalSearchInput"]::placeholder {
        color: #64748B;
        opacity: 1;
    }

    body:has(.dashboard-page) header input[x-ref="globalSearchInput"]:hover {
        border-color: #CBD5E1;
        background-color: #FFFFFF;
    }

    body:has(.dashboard-page) header input[x-ref="globalSearchInput"]:focus {
        border-color: var(--color-brand-navy);
        background-color: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(6, 31, 71, 0.12);
    }

    .dashboard-page input:not([type="checkbox"]):not([type="radio"]),
    .dashboard-page select,
    .dashboard-page button,
    .dashboard-page .role-summary a {
        min-height: 44px;
    }

    .dashboard-page input:not([type="checkbox"]):not([type="radio"]),
    .dashboard-page select {
        font-size: 16px;
    }

    .dashboard-page .role-summary a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .dashboard-page .role-summary .space-y-2 > .role-list-item {
        align-items: stretch;
        flex-direction: column;
    }

    .dashboard-page .metric-card {
        min-height: 148px;
        border-radius: 0.875rem;
        display: flex;
        flex-direction: column;
        transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
    }

    .dashboard-page a.metric-card:hover {
        transform: translateY(-2px);
    }

    .dashboard-page a.metric-card:focus-visible {
        outline: 3px solid #1D4ED8;
        outline-offset: 3px;
    }

    .dashboard-page .metric-card__header,
    .dashboard-page .metric-card__label,
    .dashboard-page .metric-card__caption {
        display: flex;
        align-items: center;
    }

    .dashboard-page .metric-card__header {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        justify-items: start;
        gap: 0.25rem;
    }

    .dashboard-page .metric-card__label {
        flex: 1 1 auto;
        min-width: 0;
        width: 100%;
        gap: 0.5rem;
        color: #475569;
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.25rem;
    }

    .dashboard-page .metric-card__label-text {
        min-width: 0;
        white-space: normal;
    }

    .dashboard-page .metric-card__value {
        margin-top: 0.375rem;
        color: var(--color-brand-navy);
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 2rem;
        letter-spacing: -0.04em;
    }

    .dashboard-page .metric-card__value-secondary {
        color: #64748B;
        font-size: 1.125rem;
        font-weight: 600;
        line-height: 1.5rem;
        letter-spacing: -0.02em;
    }

    .dashboard-page .metric-card__support {
        min-width: 0;
        margin-top: 0.5rem;
    }

    .dashboard-page .metric-card__caption {
        min-width: 0;
        min-height: 1.125rem;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .dashboard-page .metric-card__description {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
        color: #475569;
        font-size: 0.75rem;
        line-height: 1.125rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dashboard-page .metric-card__icon {
        width: 1.125rem;
        height: 1.125rem;
        flex: 0 0 auto;
        color: var(--color-brand-navy);
    }

    .dashboard-page > section {
        scroll-margin-top: 1rem;
    }

    .dashboard-page > section[aria-labelledby="role-summary-heading"] > div:first-child h2 {
        font-size: 1rem;
        line-height: 1.5rem;
        letter-spacing: -0.015em;
    }

    .dashboard-page > section[aria-labelledby="role-summary-heading"] > div:first-child p {
        margin-top: 0.125rem;
        font-size: 0.8125rem;
        line-height: 1.25rem;
        color: #64748B;
    }

    .dashboard-page .metric-card .status-badge {
        flex-shrink: 0;
        padding: 0.1875rem 0.25rem;
        font-size: 0.6875rem;
        white-space: nowrap;
    }

    .dashboard-page .metric-card__progress {
        height: 0.375rem;
        margin-top: 0.375rem;
        overflow: hidden;
        border-radius: 9999px;
        background: #E2E8F0;
    }

    .dashboard-page .metric-card__progress > div {
        height: 100%;
        border-radius: inherit;
        transition: width 160ms ease;
    }

    .dashboard-page .metric-card__progress--success > div { background: var(--color-status-success); }
    .dashboard-page .metric-card__progress--warning > div { background: var(--color-status-warning); }
    .dashboard-page .metric-card__progress--danger > div { background: var(--color-status-danger); }
    .dashboard-page .metric-card__progress--info > div { background: #3B82F6; }
    .dashboard-page .metric-card__progress--neutral > div { background: #64748B; }

    .dashboard-page .role-summary > div.surface-card[class*="border-l-"] {
        border-left-color: var(--color-brand-gold);
        border-left-width: 3px;
        border-radius: 1rem;
        padding: 1.25rem;
    }

    .dashboard-page .role-summary > div.surface-card > div:first-child {
        align-items: stretch;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
    }

    .dashboard-page .role-summary > div.surface-card > div:first-child > div:first-child {
        align-items: flex-start;
        gap: 0.875rem;
    }

    .dashboard-page .role-summary-icon {
        width: 2.75rem;
        height: 2.75rem;
        border: 1px solid #E2E8F0;
        border-radius: 0.875rem;
        background: #F8F5EE;
        color: var(--color-brand-navy);
    }

    .dashboard-page .role-summary-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .dashboard-page .role-summary h3 {
        margin-top: 0.25rem;
        font-size: 1.125rem;
        line-height: 1.6;
    }

    .dashboard-page .role-summary > div.surface-card > div:first-child > div:first-child > div:last-child > p:last-child {
        max-width: 62rem;
        margin-top: 0.25rem;
        color: #64748B;
        font-size: 0.75rem;
        line-height: 1.5;
    }

    .dashboard-page .role-summary > div.surface-card > div:first-child > div:last-child {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .dashboard-page .role-summary > div.surface-card > div:first-child > div:last-child > a {
        min-height: 40px;
        padding: 0.5rem 0.75rem;
        border-radius: 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .dashboard-page .role-summary > div.surface-card > div:first-child > div:last-child > a svg {
        width: 0.9375rem;
        height: 0.9375rem;
    }

    .dashboard-page .role-summary > div.surface-card > .grid {
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .dashboard-page .role-summary > div.surface-card > .grid > div {
        min-width: 0;
        padding: 0.875rem;
        border-radius: 0.875rem;
    }

    .dashboard-page .role-summary [class*="bg-red-"],
    .dashboard-page .role-summary [class*="bg-orange-"],
    .dashboard-page .role-summary [class*="bg-amber-"],
    .dashboard-page .role-summary [class*="bg-yellow-"],
    .dashboard-page .role-summary [class*="bg-lime-"],
    .dashboard-page .role-summary [class*="bg-green-"],
    .dashboard-page .role-summary [class*="bg-emerald-"],
    .dashboard-page .role-summary [class*="bg-teal-"],
    .dashboard-page .role-summary [class*="bg-cyan-"],
    .dashboard-page .role-summary [class*="bg-sky-"],
    .dashboard-page .role-summary [class*="bg-blue-"],
    .dashboard-page .role-summary [class*="bg-indigo-"],
    .dashboard-page .role-summary [class*="bg-violet-"],
    .dashboard-page .role-summary [class*="bg-purple-"],
    .dashboard-page .role-summary [class*="bg-fuchsia-"],
    .dashboard-page .role-summary [class*="bg-pink-"],
    .dashboard-page .role-summary [class*="bg-rose-"] {
        background-color: #F8FAFC;
        color: var(--color-brand-navy);
    }

    .dashboard-page .role-summary [class*="text-red-"],
    .dashboard-page .role-summary [class*="text-orange-"],
    .dashboard-page .role-summary [class*="text-amber-"],
    .dashboard-page .role-summary [class*="text-yellow-"],
    .dashboard-page .role-summary [class*="text-lime-"],
    .dashboard-page .role-summary [class*="text-green-"],
    .dashboard-page .role-summary [class*="text-emerald-"],
    .dashboard-page .role-summary [class*="text-teal-"],
    .dashboard-page .role-summary [class*="text-cyan-"],
    .dashboard-page .role-summary [class*="text-sky-"],
    .dashboard-page .role-summary [class*="text-blue-"],
    .dashboard-page .role-summary [class*="text-indigo-"],
    .dashboard-page .role-summary [class*="text-violet-"],
    .dashboard-page .role-summary [class*="text-purple-"],
    .dashboard-page .role-summary [class*="text-fuchsia-"],
    .dashboard-page .role-summary [class*="text-pink-"],
    .dashboard-page .role-summary [class*="text-rose-"] {
        color: var(--color-brand-navy);
    }

    .dashboard-page .role-summary [class*="border-red-"],
    .dashboard-page .role-summary [class*="border-orange-"],
    .dashboard-page .role-summary [class*="border-amber-"],
    .dashboard-page .role-summary [class*="border-yellow-"],
    .dashboard-page .role-summary [class*="border-lime-"],
    .dashboard-page .role-summary [class*="border-green-"],
    .dashboard-page .role-summary [class*="border-emerald-"],
    .dashboard-page .role-summary [class*="border-teal-"],
    .dashboard-page .role-summary [class*="border-cyan-"],
    .dashboard-page .role-summary [class*="border-sky-"],
    .dashboard-page .role-summary [class*="border-blue-"],
    .dashboard-page .role-summary [class*="border-indigo-"],
    .dashboard-page .role-summary [class*="border-violet-"],
    .dashboard-page .role-summary [class*="border-purple-"],
    .dashboard-page .role-summary [class*="border-fuchsia-"],
    .dashboard-page .role-summary [class*="border-pink-"],
    .dashboard-page .role-summary [class*="border-rose-"] {
        border-color: #E2E8F0;
    }

    .dashboard-page .role-summary .status-badge {
        background: #F1F5F9;
        color: #334155;
        border-color: #E2E8F0;
    }

    .dashboard-page .role-summary .status-badge::before {
        content: "";
        width: 0.375rem;
        height: 0.375rem;
        flex: 0 0 auto;
        border-radius: 9999px;
        background: var(--color-brand-gold);
    }

    .dashboard-page .role-summary a[class*="bg-"] {
        border: 1px solid #E2E8F0;
        box-shadow: none;
        transition: background-color 160ms ease, border-color 160ms ease;
    }

    .dashboard-page .role-summary a[class*="bg-"]:hover {
        background-color: #F1F5F9;
        border-color: #CBD5E1;
    }

    .dashboard-page a.metric-card:hover {
        transform: translateY(-2px);
        border-color: #CBD5E1;
        box-shadow: 0 8px 20px rgba(6, 31, 71, 0.08);
    }

    .dashboard-page input:not([type="checkbox"]):not([type="radio"]).dashboard-filter-control,
    .dashboard-page select.dashboard-filter-control {
        height: 40px;
        min-height: 40px;
        padding: 0.5rem 0.625rem;
        font-size: 16px;
    }

    .dashboard-page .dashboard-filter-panel {
        padding: 0.625rem 0.75rem;
        border-radius: 0.875rem;
    }

    .dashboard-page .dashboard-filter-heading {
        display: none;
    }

    .dashboard-page .dashboard-filter-field {
        min-width: 0;
    }

    .dashboard-page .dashboard-filter-field > label {
        display: block;
        margin: 0 0 0.375rem 0.125rem;
        color: #64748B;
        font-size: 0.6875rem;
        font-weight: 600;
        line-height: 1rem;
    }

    .dashboard-page .dashboard-filter-control {
        min-width: 0;
        border-color: #E2E8F0;
        border-radius: 0.75rem;
        background-color: #F8FAFC;
        color: var(--color-brand-navy);
        transition: border-color 160ms ease, background-color 160ms ease, box-shadow 160ms ease;
    }

    .dashboard-page select.dashboard-filter-control {
        height: 40px;
        min-height: 40px;
        padding: 0 2rem 0 0.75rem;
        font-size: 0.8125rem !important;
        font-weight: 500;
    }

    .dashboard-page .dashboard-date-control {
        position: relative;
        display: flex;
        height: 40px;
        min-height: 40px;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0 0.75rem;
        cursor: pointer;
    }

    .dashboard-page .dashboard-date-control:hover {
        border-color: #CBD5E1;
        background-color: #FFFFFF;
    }

    .dashboard-page .dashboard-date-control:focus-within {
        border-color: var(--color-brand-navy);
        outline: 3px solid rgba(29, 78, 216, 0.5);
        outline-offset: 2px;
        background-color: #FFFFFF;
    }

    .dashboard-page .dashboard-date-native {
        position: absolute;
        z-index: 2;
        inset: 0;
        width: 100%;
        height: 100%;
        min-height: 40px;
        opacity: 0.01;
        cursor: pointer;
    }

    .dashboard-page .dashboard-date-display {
        overflow: hidden;
        color: #334155;
        font-size: 0.8125rem;
        font-weight: 500;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dashboard-page .dashboard-date-display[data-empty="true"] {
        color: #64748B;
    }

    .dashboard-page .dashboard-date-control > svg {
        width: 1rem;
        height: 1rem;
        flex: 0 0 auto;
        color: #64748B;
    }

    .dashboard-page .dashboard-filter-control:hover {
        border-color: #CBD5E1;
        background-color: #FFFFFF;
    }

    .dashboard-page .dashboard-filter-heading {
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-width: 0;
        align-self: end;
        height: 40px;
        color: var(--color-brand-navy);
        font-size: 0.8125rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .dashboard-page .dashboard-filter-actions > * {
        height: 40px;
        min-height: 40px;
        border-radius: 0.75rem;
        padding: 0 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .dashboard-page .dashboard-filter-actions .btn-primary {
        background-color: var(--color-brand-navy);
        color: #FFFFFF;
    }

    .dashboard-page .dashboard-filter-actions .btn-primary:hover {
        background-color: #0B2D60;
        filter: none;
    }

    .dashboard-page .dashboard-filter-actions .btn-secondary {
        background-color: #F8FAFC;
        color: #334155;
    }

    .dashboard-page .dashboard-filter-actions .btn-secondary:hover {
        background-color: #F1F5F9;
    }

    .dashboard-page .dashboard-filter-panel > summary {
        list-style: none;
    }

    .dashboard-page .dashboard-filter-panel > summary::-webkit-details-marker {
        display: none;
    }

    .dashboard-page .dashboard-filter-panel[open] > summary {
        margin-bottom: 0.5rem;
    }

    @media (min-width: 1280px) {
        .dashboard-page .dashboard-filter-panel > summary {
            display: none;
        }

        .dashboard-page .dashboard-filter-heading {
            display: flex;
        }

        .dashboard-page .dashboard-filter-grid {
            display: grid;
            grid-template-columns: minmax(112px, 0.75fr) repeat(2, minmax(126px, 1fr)) repeat(2, minmax(150px, 1.15fr)) minmax(164px, 1.2fr);
            gap: 0.5rem;
            align-items: end;
        }

        .dashboard-page .dashboard-filter-actions {
            align-self: end;
        }

        .dashboard-page .dashboard-filter-actions .btn-primary,
        .dashboard-page .dashboard-filter-actions .btn-secondary {
            padding-left: 0.625rem;
            padding-right: 0.625rem;
        }
    }

    @media (min-width: 640px) {
        .dashboard-page .role-summary .space-y-2 > .role-list-item {
            align-items: center;
            flex-direction: row;
        }
    }

    @media (min-width: 1024px) {
        .dashboard-page .role-summary > div.surface-card > div:first-child {
            flex-direction: row;
            align-items: center;
        }

        @media (min-width: 1280px) and (max-width: 1535px) {
            .dashboard-page .metric-card__icon {
                display: none;
            }
        }

        .dashboard-page .role-summary > div.surface-card > div:first-child > div:last-child {
            justify-content: flex-end;
        }
    }

    @media (max-width: 639px) {
        .dashboard-page .metric-card {
            min-height: 161px;
        }

        .dashboard-page .role-summary > div.surface-card {
            padding: 1rem;
        }

        .dashboard-page .role-summary > div.surface-card > div:first-child > div:last-child > a {
            flex: 1 1 100%;
        }
    }

    .dashboard-page .role-summary > div.surface-card.role-summary-admin {
        padding: 1.5rem;
        border-left-color: var(--color-brand-gold);
        border-left-width: 3px;
        border-radius: 1rem;
    }

    .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__header {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
    }

    .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__header > div.role-summary-admin__identity {
        align-items: flex-start;
        gap: 0.625rem;
        min-width: 0;
    }

    .dashboard-page .role-summary-admin .role-summary-admin__title-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .dashboard-page .role-summary-admin .role-summary-icon {
        width: 1.25rem;
        height: 1.25rem;
        flex: 0 0 1.25rem;
        border: 0;
        border-radius: 0;
        background: transparent;
        color: var(--color-brand-navy);
    }

    .dashboard-page .role-summary-admin h3 {
        margin: 0;
        font-size: 1.25rem;
        line-height: 1.4;
    }

    .dashboard-page .role-summary-admin .role-summary-admin__description {
        display: -webkit-box;
        max-width: 62rem;
        margin-top: 0.25rem;
        overflow: hidden;
        color: #475569;
        font-size: 0.875rem;
        line-height: 1.5;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__header > div.role-summary-admin__actions {
        align-self: flex-start;
        justify-content: flex-start;
        gap: 0.5rem;
    }

    .dashboard-page .role-summary-admin .role-summary-admin__action {
        min-height: 40px;
        height: 40px;
        padding: 0 0.75rem;
        border: 1px solid #CBD5E1;
        border-radius: 0.625rem;
        background: #FFFFFF;
        color: #334155;
        font-size: 0.75rem;
        font-weight: 600;
        box-shadow: none;
        white-space: nowrap;
    }

    .dashboard-page .role-summary-admin .role-summary-admin__action:hover {
        background: #F1F5F9;
        border-color: #94A3B8;
    }

    .dashboard-page .role-summary-admin .role-summary-admin__action:focus-visible {
        outline: 2px solid var(--color-brand-navy);
        outline-offset: 2px;
    }

    .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__stats {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1rem;
        margin-bottom: 0;
    }

    .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__stats > div.role-summary-admin__stat {
        min-width: 0;
        padding: 1rem;
        border: 1px solid #F1F5F9;
        border-radius: 0.75rem;
        background: #F8FAFC;
        color: var(--color-brand-navy);
    }

    .dashboard-page .role-summary-admin .role-summary-admin__label {
        display: block;
        overflow: hidden;
        color: #334155;
        font-size: 0.8125rem;
        font-weight: 500;
        line-height: 1.4;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dashboard-page .role-summary-admin .role-summary-admin__value {
        display: flex;
        align-items: baseline;
        gap: 0.25rem;
        margin-top: 0.5rem;
        color: var(--color-brand-navy);
        font-family: var(--font-display);
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.25;
        white-space: nowrap;
    }

    .dashboard-page .role-summary-admin .role-summary-admin__unit,
    .dashboard-page .role-summary-admin .role-summary-admin__ratio {
        color: #64748B;
        font-family: inherit;
        font-size: 0.75rem;
        font-weight: 400;
        line-height: 1.25;
    }

    .dashboard-page .role-summary-admin .role-summary-admin__detail {
        display: -webkit-box;
        min-height: 2.8em;
        margin-top: 0.5rem;
        overflow: hidden;
        color: #475569;
        font-size: 0.8125rem;
        line-height: 1.4;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    @media (min-width: 640px) {
        .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }

    @media (min-width: 1024px) {
        .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__header {
            flex-direction: row;
            align-items: flex-start;
        }

        .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__header > div.role-summary-admin__actions {
            justify-content: flex-end;
        }
    }

    @media (min-width: 1280px) {
        .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__stats {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 639px) {
        .dashboard-page .role-summary > div.surface-card.role-summary-admin {
            padding: 1.5rem;
        }

        .dashboard-page .role-summary > div.role-summary-admin > div.role-summary-admin__header > div.role-summary-admin__actions > a.role-summary-admin__action {
            flex: 1 1 11rem;
        }
    }
</style>
@endpush

@section('content')
    @php
        $role = auth()->user()->role;
        $plannedPortions = $todayProduction?->planned_portions ?? ($todayMenu?->target_portions ?? 0);
        $actualPortions = $todayProduction?->actual_good_portions ?? 0;
        $variance = $todayProduction?->portion_variance ?? 0;
        $apdScore = $latestQc?->apd_compliance_score;
        $hasDashboardProductionData = $todayProduction !== null || $todayMenu !== null;
        $dashboardPlannedPortions = $plannedPortions;
        $dashboardActualPortions = $actualPortions;
        $dashboardPortionVariance = $dashboardActualPortions - $dashboardPlannedPortions;
        $dashboardApdScore = $apdScore;
        $confirmedCount = $deliveries->whereIn('status', ['confirmed_clean', 'confirmed_with_issue'])->count();
        $inTransitCount = $deliveries->where('status', 'in_transit')->count();
        $arrivedCount = $deliveries->where('status', 'arrived')->count();
        $preparingCount = $deliveries->where('status', 'preparing')->count();
        $totalDeliveries = $deliveries->count();
        $deliveryRate = $totalDeliveries > 0 ? round(($confirmedCount / $totalDeliveries) * 100) : 0;
        $deliverySummaryParts = collect([
            'menunggu' => $preparingCount,
            'berjalan' => $inTransitCount,
            'tiba' => $arrivedCount,
            'terkonfirmasi' => $confirmedCount,
        ])->filter(fn (int $count) => $count > 0)
            ->map(fn (int $count, string $label) => number_format($count, 0, ',', '.') . ' ' . $label);
        $deliverySummary = $deliverySummaryParts->isNotEmpty()
            ? $deliverySummaryParts->implode(' · ')
            : 'Belum ada status distribusi';
        $openComplaintsCount = $activeComplaints->count();
        $recipientCategories = $allInstitutions->pluck('recipient_category')->filter()->unique();
        $canViewProduction = in_array($role->value, ['admin', 'kepala_dapur', 'asisten_lapangan', 'ahli_gizi']);
        $canViewDistribution = in_array($role->value, ['admin', 'tim_distribusi', 'guru_pic', 'asisten_lapangan', 'kepala_dapur']);
        $canViewApd = in_array($role->value, ['admin', 'ahli_gizi', 'kepala_dapur', 'asisten_lapangan']);
        $portionStatus = !$hasDashboardProductionData
            ? ['class' => 'status-badge--neutral', 'label' => 'Tanpa data']
            : ($dashboardPortionVariance === 0
                ? ['class' => 'status-badge--success', 'label' => 'Sesuai target']
                : ['class' => 'status-badge--warning', 'label' => 'Perlu ditinjau']);
        $distributionStatus = $totalDeliveries === 0
            ? ['class' => 'status-badge--neutral', 'label' => 'Belum ada data']
            : ($confirmedCount >= $totalDeliveries
                ? ['class' => 'status-badge--success', 'label' => 'Selesai']
                : (($inTransitCount + $arrivedCount) > 0
                    ? ['class' => 'status-badge--info', 'label' => 'Berjalan']
                    : ['class' => 'status-badge--warning', 'label' => 'Menunggu']));
        $deliveryProgressTone = str_replace('status-badge--', '', $distributionStatus['class']);
        $apdStatus = $dashboardApdScore === null
            ? ['class' => 'status-badge--neutral', 'label' => 'Belum ada data']
            : ((float) $dashboardApdScore >= 100
                ? ['class' => 'status-badge--success', 'label' => 'Sesuai']
                : ['class' => 'status-badge--warning', 'label' => 'Perlu perhatian']);
        $complaintsStatus = $criticalComplaintsCount > 0
            ? ['class' => 'status-badge--danger', 'label' => 'Kritis']
            : ($openComplaintsCount > 0
                ? ['class' => 'status-badge--warning', 'label' => 'Perlu perhatian']
                : ['class' => 'status-badge--success', 'label' => 'Tidak ada keluhan']);
    @endphp

    <div class="dashboard-page flex flex-col gap-4">

        @if($criticalComplaintsCount > 0)
            <section class="order-1 flex flex-col gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 sm:flex-row sm:items-center sm:justify-between" role="status">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-800" aria-hidden="true">!</span>
                    <div>
                        <h2 class="font-bold text-red-900">{{ $criticalComplaintsCount }} keluhan prioritas perlu ditindaklanjuti</h2>
                        <p class="mt-1 text-sm text-red-800">Periksa tiket keluhan untuk melihat rincian dan status tindak lanjut.</p>
                    </div>
                </div>
                @if(in_array($role->value, ['tim_distribusi', 'guru_pic', 'asisten_lapangan', 'kepala_dapur']))
                    <a href="{{ route('distribution.index', ['tab' => 'complaints']) }}" class="btn-secondary min-h-touch w-full border-red-300 text-red-900 sm:w-auto">
                        Lihat tiket keluhan
                    </a>
                @endif
            </section>
        @endif

        <section class="order-2" aria-label="Filter ringkasan">
            <details class="dashboard-filter-panel surface-card rounded-2xl" open
                     x-init="$el.open = window.matchMedia('(min-width: 1280px)').matches">
                <summary class="flex min-h-touch cursor-pointer items-center justify-between gap-3 text-sm font-bold text-brand-navy">
                    <span>Filter ringkasan</span>
                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600">
                        <span>Atur rentang dan penerima</span>
                        <svg class="h-4 w-4 transition-transform" :class="$el.parentElement.open ? 'rotate-180' : ''" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </span>
                </summary>

                <form method="GET" action="{{ route('dashboard') }}" class="dashboard-filter-grid grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <div class="dashboard-filter-heading">
                        <span>Filter ringkasan</span>
                        <button type="button" class="inline-flex h-6 w-6 items-center justify-center rounded-full text-slate-600 transition hover:bg-slate-100" aria-label="Informasi filter ringkasan" title="Periode dan kategori memfilter seluruh metrik; lokasi memfilter distribusi dan keluhan. Ringkasan tugas tetap menampilkan antrean aktif.">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 11v5m0-8h.01"/></svg>
                        </button>
                    </div>
                    <div class="dashboard-filter-field">
                        <label for="date_from">Tanggal mulai</label>
                        <div class="dashboard-date-control dashboard-filter-control" x-data="{
                            selectedDate: @js($dateFrom),
                            get displayDate() {
                                if (!this.selectedDate) return 'Pilih tanggal';
                                return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' })
                                    .format(new Date(`${this.selectedDate}T00:00:00Z`));
                            }
                        }">
                            <input id="date_from" type="date" name="date_from" value="{{ $dateFrom }}" x-model="selectedDate" class="dashboard-date-native" aria-label="Tanggal mulai">
                            <span class="dashboard-date-display" :data-empty="!selectedDate" x-text="displayDate">Pilih tanggal</span>
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="5" width="17" height="16" rx="2"/><path stroke-linecap="round" d="M7.5 3v4M16.5 3v4M3.5 9.5h17"/></svg>
                        </div>
                    </div>
                    <div class="dashboard-filter-field">
                        <label for="date_to">Tanggal akhir</label>
                        <div class="dashboard-date-control dashboard-filter-control" x-data="{
                            selectedDate: @js($dateTo),
                            get displayDate() {
                                if (!this.selectedDate) return 'Pilih tanggal';
                                return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' })
                                    .format(new Date(`${this.selectedDate}T00:00:00Z`));
                            }
                        }">
                            <input id="date_to" type="date" name="date_to" value="{{ $dateTo }}" x-model="selectedDate" class="dashboard-date-native" aria-label="Tanggal akhir">
                            <span class="dashboard-date-display" :data-empty="!selectedDate" x-text="displayDate">Pilih tanggal</span>
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="5" width="17" height="16" rx="2"/><path stroke-linecap="round" d="M7.5 3v4M16.5 3v4M3.5 9.5h17"/></svg>
                        </div>
                    </div>
                    <div class="dashboard-filter-field">
                        <label for="institution_id">Lokasi penerima</label>
                        <select id="institution_id" name="institution_id" class="form-control dashboard-filter-control">
                            <option value="all">Semua lokasi</option>
                            @foreach($allInstitutions as $institution)
                                <option value="{{ $institution->id }}" @selected((string) $institutionId === (string) $institution->id)>
                                    {{ $institution->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dashboard-filter-field">
                        <label for="recipient_category">Kategori penerima</label>
                        <select id="recipient_category" name="recipient_category" class="form-control dashboard-filter-control">
                            <option value="all">Semua kategori</option>
                            @foreach($recipientCategories as $category)
                                <option value="{{ $category }}" @selected($recipientCategory === $category)>
                                    {{ $allInstitutions->firstWhere('recipient_category', $category)?->recipientCategoryLabel() ?? $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dashboard-filter-actions flex items-end gap-2 pt-1 sm:col-span-2 xl:col-span-1">
                        <button type="submit" class="btn-primary flex-1 px-3 text-sm">Terapkan</button>
                        <a href="{{ route('dashboard') }}" class="btn-secondary flex-1 px-3 text-sm">Atur ulang</a>
                    </div>
                </form>
            </details>
        </section>

        <section class="order-3" aria-labelledby="dashboard-metrics-heading">
            <div class="mb-2">
                <h2 id="dashboard-metrics-heading" class="text-lg font-extrabold text-brand-navy">Ringkasan operasional</h2>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @if($canViewProduction)
                    <a href="{{ route('production-inventory.index') }}" class="metric-card surface-card rounded-2xl p-4 sm:p-5" aria-label="Buka Produksi dan bahan baku untuk melihat selisih porsi">
                @else
                    <article class="metric-card surface-card rounded-2xl p-4 sm:p-5">
                @endif
                    <div class="metric-card__header">
                        <div class="metric-card__label">
                            <svg class="metric-card__icon" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h10M4 17h6"/><path stroke-linecap="round" stroke-linejoin="round" d="m17 14 3 3-3 3"/></svg>
                            <span class="metric-card__label-text">Selisih porsi</span>
                        </div>
                        <span class="status-badge {{ $portionStatus['class'] }}">{{ $portionStatus['label'] }}</span>
                    </div>
                    @if($hasDashboardProductionData)
                        <p class="metric-card__value tabular-nums">
                            {{ $dashboardPortionVariance > 0 ? '+' : '' }}{{ number_format($dashboardPortionVariance, 0, ',', '.') }}
                        </p>
                    @else
                        <p class="metric-card__value">Tidak ada data</p>
                    @endif
                    <div class="metric-card__support">
                        <div class="metric-card__caption">
                            <p class="metric-card__description" title="{{ $hasDashboardProductionData ? number_format($dashboardActualPortions, 0, ',', '.') . ' / ' . number_format($dashboardPlannedPortions, 0, ',', '.') . ' porsi' : 'Belum ada menu atau data produksi' }}">
                                {{ $hasDashboardProductionData ? number_format($dashboardActualPortions, 0, ',', '.') . ' / ' . number_format($dashboardPlannedPortions, 0, ',', '.') . ' porsi' : 'Belum ada menu atau data produksi' }}
                            </p>
                        @isset($dashboardTrends['portion_variance'])
                            <span class="shrink-0 text-xs font-semibold text-slate-600">{{ $dashboardTrends['portion_variance'] }}</span>
                        @endisset
                        </div>
                    </div>
                @if($canViewProduction)</a>@else</article>@endif

                @if($canViewDistribution)
                    <a href="{{ route('distribution.index') }}" class="metric-card surface-card rounded-2xl p-4 sm:p-5" aria-label="Buka Distribusi dan keluhan untuk melihat status distribusi">
                @else
                    <article class="metric-card surface-card rounded-2xl p-4 sm:p-5">
                @endif
                    <div class="metric-card__header">
                        <div class="metric-card__label">
                            <svg class="metric-card__icon" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/><circle cx="7.5" cy="18" r="1.5"/><circle cx="17.5" cy="18" r="1.5"/></svg>
                            <span class="metric-card__label-text">Status distribusi</span>
                        </div>
                        <span class="status-badge {{ $distributionStatus['class'] }}">{{ $distributionStatus['label'] }}</span>
                    </div>
                    <p class="metric-card__value tabular-nums">
                        {{ number_format($confirmedCount, 0, ',', '.') }} <span class="metric-card__value-secondary">/ {{ number_format($totalDeliveries, 0, ',', '.') }}</span>
                    </p>
                    <div class="metric-card__support">
                        <div class="metric-card__caption">
                            <p class="metric-card__description" title="{{ $deliverySummary }}">{{ $deliverySummary }}</p>
                        @isset($dashboardTrends['distribution'])
                            <span class="shrink-0 text-xs font-semibold text-slate-600">{{ $dashboardTrends['distribution'] }}</span>
                        @endisset
                        </div>
                        <div class="metric-card__progress metric-card__progress--{{ $deliveryProgressTone }}" role="progressbar" aria-label="Persentase distribusi yang dikonfirmasi" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $deliveryRate }}">
                            <div style="width: {{ $deliveryRate }}%"></div>
                        </div>
                    </div>
                @if($canViewDistribution)</a>@else</article>@endif

                @if($canViewApd)
                    <a href="{{ route('apd-inspection.index') }}" class="metric-card surface-card rounded-2xl p-4 sm:p-5" aria-label="Buka Cek APD untuk melihat kepatuhan">
                @else
                    <article class="metric-card surface-card rounded-2xl p-4 sm:p-5">
                @endif
                    <div class="metric-card__header">
                        <div class="metric-card__label">
                            <svg class="metric-card__icon" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.5 3 7.5 7 10 4-2.5 7-5.5 7-10V6l-7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                            <span class="metric-card__label-text">Kepatuhan APD</span>
                        </div>
                        <span class="status-badge {{ $apdStatus['class'] }}">{{ $apdStatus['label'] }}</span>
                    </div>
                    @if($dashboardApdScore !== null)
                        <p class="metric-card__value tabular-nums">{{ number_format((float) $dashboardApdScore, 0, ',', '.') }}%</p>
                    @else
                        <p class="metric-card__value">Belum ada data</p>
                    @endif
                    <div class="metric-card__support">
                        <div class="metric-card__caption">
                            <p class="metric-card__description" title="{{ $dashboardApdScore !== null ? 'Berdasarkan QC terbaru' : 'Pemeriksaan belum tercatat' }}">
                                {{ $dashboardApdScore !== null ? 'Berdasarkan QC terbaru' : 'Pemeriksaan belum tercatat' }}
                            </p>
                        @isset($dashboardTrends['apd'])
                            <span class="shrink-0 text-xs font-semibold text-slate-600">{{ $dashboardTrends['apd'] }}</span>
                        @endisset
                        </div>
                    </div>
                @if($canViewApd)</a>@else</article>@endif

                @if($canViewDistribution)
                    <a href="{{ route('distribution.index', ['tab' => 'complaints']) }}" class="metric-card surface-card rounded-2xl p-4 sm:p-5" aria-label="Buka tiket keluhan terbuka">
                @else
                    <article class="metric-card surface-card rounded-2xl p-4 sm:p-5">
                @endif
                    <div class="metric-card__header">
                        <div class="metric-card__label">
                            <svg class="metric-card__icon" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 2.8 19h18.4L12 3Z"/><path stroke-linecap="round" stroke-width="2" d="M12 9v4m0 3h.01"/></svg>
                            <span class="metric-card__label-text">Keluhan terbuka</span>
                        </div>
                        <span class="status-badge {{ $complaintsStatus['class'] }}">{{ $complaintsStatus['label'] }}</span>
                    </div>
                    <p class="metric-card__value tabular-nums">{{ number_format($openComplaintsCount, 0, ',', '.') }}</p>
                    <div class="metric-card__support">
                        <div class="metric-card__caption">
                            <p class="metric-card__description" title="{{ number_format($criticalComplaintsCount, 0, ',', '.') }} kritis">{{ number_format($criticalComplaintsCount, 0, ',', '.') }} kritis</p>
                        @isset($dashboardTrends['complaints'])
                            <span class="shrink-0 text-xs font-semibold text-slate-600">{{ $dashboardTrends['complaints'] }}</span>
                        @endisset
                        </div>
                    </div>
                @if($canViewDistribution)</a>@else</article>@endif
            </div>
        </section>

        <section class="order-4" aria-labelledby="role-summary-heading">
            <div class="mb-3">
                <h2 id="role-summary-heading" class="text-lg font-extrabold text-brand-navy">Ringkasan tugas</h2>
                <p class="mt-1 text-sm text-slate-600">Tugas dan tindakan sesuai peran akun Anda.</p>
            </div>

            <div class="role-summary">
            @if($role->value === 'ahli_gizi')
                @include('dashboard.partials.role-ahli-gizi')
            @elseif($role->value === 'akuntan')
                @include('dashboard.partials.role-akuntan')
            @elseif($role->value === 'kepala_dapur')
                @include('dashboard.partials.role-kepala-dapur')
            @elseif($role->value === 'asisten_lapangan')
                @include('dashboard.partials.role-asisten-lapangan')
            @elseif($role->value === 'tim_distribusi')
                @include('dashboard.partials.role-tim-distribusi')
            @elseif($role->value === 'guru_pic')
                @include('dashboard.partials.role-guru-pic')
            @else
                @include('dashboard.partials.role-admin')
            @endif
            </div>
        </section>
    </div>
@endsection
