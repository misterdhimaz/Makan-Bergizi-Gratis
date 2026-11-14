@extends('layouts.app')

@section('title', 'Perencanaan & Validasi Menu')
@section('page_title', 'Perencanaan Menu')

@push('styles')
<style>
    .menu-planning-page input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
    .menu-planning-page select,
    .menu-planning-page textarea,
    .menu-planning-dialog input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
    .menu-planning-dialog select,
    .menu-planning-dialog textarea {
        min-height: 44px;
        font-size: 16px;
    }

    .menu-planning-page button,
    .menu-planning-dialog button {
        min-height: 44px;
    }

    .menu-planning-page textarea,
    .menu-planning-dialog textarea {
        min-height: 96px;
    }

    .menu-planning-page .planning-stages {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .menu-planning-page .planning-stage-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .menu-planning-page .planning-stage-header-title {
        margin: 0;
        color: #0F2854;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .menu-planning-page .planning-stage-header-subtitle {
        margin: 0.25rem 0 0;
        color: #64748B;
        font-size: 0.8125rem;
        line-height: 1.5;
    }

    .menu-planning-page .planning-stage-header-action {
        display: inline-flex;
        min-height: 44px !important;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        gap: 0.625rem;
        padding: 0 1.25rem;
        border: 0;
        border-radius: 0.75rem;
        background: #C8A96A;
        color: #061F47;
        font-size: 0.8125rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .menu-planning-page .planning-stage-header-action:hover {
        background: #D7B978;
    }

    .menu-planning-page .planning-stage-header-action:focus-visible {
        outline: 2px solid #061F47;
        outline-offset: 3px;
    }

    .menu-planning-page .planning-stage-card {
        position: relative;
        display: grid;
        container-type: inline-size;
        min-width: 0;
        grid-template-rows: 2.75rem 1.5rem minmax(2.625rem, auto) auto;
        row-gap: 0.75rem;
        padding: 1.5rem;
        border-radius: 1rem;
    }

    .menu-planning-page .planning-stage-heading {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 0.75rem;
        grid-row: 1;
    }

    .menu-planning-page .planning-stage-marker {
        display: inline-flex;
        width: 2.25rem;
        height: 2.25rem;
        flex: 0 0 2.25rem;
        align-items: center;
        justify-content: center;
        border: 1px solid #CBD5E1;
        border-radius: 9999px;
        background: #F8FAFC;
        color: #334155;
        font-size: 0.875rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .menu-planning-page .planning-stage-marker--ready {
        border-color: #BBF7D0;
        background: #F0FDF4;
        color: #166534;
    }

    .menu-planning-page .planning-stage-title {
        min-width: 0;
        color: #0F2854;
        font-size: 0.9375rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .menu-planning-page .planning-stage-role {
        display: inline-flex;
        width: fit-content;
        max-width: calc(100% - 3rem);
        align-items: center;
        gap: 0.25rem;
        grid-row: 2;
        margin: 0 0 0 3rem;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        background: #F1F5F9;
        color: #334155;
        font-size: 0.6875rem;
        font-weight: 500;
        line-height: 1.25rem;
        white-space: nowrap;
    }

    .menu-planning-page .planning-stage-description {
        display: -webkit-box;
        min-height: 2.625rem;
        grid-row: 3;
        margin: 0;
        overflow: hidden;
        color: #475569;
        font-size: 0.8125rem;
        line-height: 1.5;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .menu-planning-page .planning-stage-status {
        display: grid;
        min-width: 0;
        grid-template-columns: minmax(0, 1fr) auto;
        grid-template-rows: auto auto;
        align-items: center;
        gap: 0.25rem 0.75rem;
        grid-row: 4;
        margin: 0;
        font-size: 0.75rem;
        line-height: 1.25rem;
    }

    .menu-planning-page .planning-stage-status-label {
        grid-column: 1 / -1;
        grid-row: 1;
        min-width: 0;
        overflow: hidden;
        color: #64748B;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .menu-planning-page .planning-stage-status-label-long {
        display: none;
    }

    @container (min-width: 260px) {
        .menu-planning-page .planning-stage-status-label-long {
            display: inline;
        }

        .menu-planning-page .planning-stage-status-label-short {
            display: none;
        }
    }

    .menu-planning-page .planning-stage-status-value {
        display: inline-flex;
        grid-column: 1;
        grid-row: 2;
        align-items: baseline;
        gap: 0.375rem;
        color: #64748B;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .menu-planning-page .planning-stage-status-number {
        color: #0F2854;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .menu-planning-page .planning-stage-status-unit {
        color: #64748B;
        font-size: 0.75rem;
        font-weight: 400;
    }

    .menu-planning-page .planning-stage-status-value--attention {
        color: #B45309;
    }

    .menu-planning-page .planning-stage-status-value--attention .planning-stage-status-number {
        color: #B45309;
    }

    .menu-planning-page .planning-stage-status-value--info {
        color: #1D4ED8;
    }

    .menu-planning-page .planning-stage-status-value--info .planning-stage-status-number {
        color: #1D4ED8;
    }

    .menu-planning-page .planning-stage-status-value--success {
        color: #15803D;
    }

    .menu-planning-page .planning-stage-status-value--success .planning-stage-status-number {
        color: #15803D;
    }

    .menu-planning-page .planning-stage-status-badge {
        grid-column: 2;
        grid-row: 2;
        justify-self: end;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        background: #F1F5F9;
        color: #475569;
        font-size: 0.6875rem;
        font-weight: 500;
        line-height: 1.25rem;
        white-space: nowrap;
    }

    .menu-planning-page .planning-stage-status-badge--ready {
        background: #DCFCE7;
        color: #166534;
    }

    .menu-planning-page .planning-chart-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        border-radius: 1rem;
    }

    .menu-planning-page .planning-chart-subtitle {
        color: #475569;
        font-size: 0.8125rem;
        line-height: 1.4;
    }

    .menu-planning-page .planning-nutrition-chart {
        min-height: 13rem;
        flex: 1 1 auto;
    }

    .menu-planning-page .planning-status-chart {
        min-height: 10rem;
        flex: 1 1 auto;
    }

    .menu-planning-page .planning-status-legend {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.5rem 1rem;
        margin-top: 1rem;
    }

    .menu-planning-page .planning-status-legend-item {
        display: flex;
        min-width: 0;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        color: #334155;
        font-size: 0.8125rem;
    }

    .menu-planning-page .planning-status-legend-item.is-zero {
        color: #64748B;
    }

    .menu-planning-page .planning-status-legend-name {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 0.5rem;
    }

    .menu-planning-page .planning-status-legend-swatch {
        width: 0.625rem;
        height: 0.625rem;
        flex: 0 0 0.625rem;
        border-radius: 9999px;
    }

    .menu-planning-page .planning-status-legend-count {
        flex: 0 0 auto;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
    }

    .menu-planning-page .menu-filter-card,
    .menu-planning-page .menu-detail-card {
        padding: 1.5rem;
        border-radius: 1rem;
    }

    .menu-planning-page .menu-filter-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        align-items: end;
        gap: 0.75rem;
    }

    .menu-planning-page .menu-filter-label {
        display: block;
        margin-bottom: 0.375rem;
        color: #475569;
        font-size: 0.8125rem;
        font-weight: 600;
    }

    .menu-planning-page .menu-filter-control,
    .menu-planning-page .menu-filter-submit {
        box-sizing: border-box;
        width: 100%;
        height: 40px;
        min-height: 40px !important;
        border-radius: 0.625rem;
        font-size: 0.8125rem !important;
    }

    .menu-planning-page .menu-filter-control {
        min-width: 0;
        padding: 0 0.75rem;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        color: #0F172A;
    }

    .menu-planning-page .menu-filter-control:focus-visible {
        border-color: #1D4ED8;
        outline: 2px solid rgba(29, 78, 216, 0.35);
        outline-offset: 2px;
    }

    .menu-planning-page .menu-filter-search {
        position: relative;
    }

    .menu-planning-page .menu-filter-search svg {
        position: absolute;
        top: 50%;
        left: 0.75rem;
        width: 1rem;
        height: 1rem;
        color: #64748B;
        pointer-events: none;
        transform: translateY(-50%);
    }

    .menu-planning-page .menu-filter-search .menu-filter-control {
        padding-left: 2.25rem;
    }

    .menu-planning-page .menu-filter-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0 0.875rem;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        color: #0F2854;
        font-weight: 700;
        cursor: pointer;
    }

    .menu-planning-page .menu-filter-submit:hover {
        background: #F8FAFC;
    }

    .menu-planning-page .menu-filter-submit:focus-visible {
        outline: 2px solid rgba(29, 78, 216, 0.45);
        outline-offset: 2px;
    }

    .menu-planning-page .menu-card-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.5rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid #E2E8F0;
    }

    .menu-planning-page .menu-card-identity,
    .menu-planning-page .menu-card-side {
        min-width: 0;
    }

    .menu-planning-page .menu-card-eyebrow,
    .menu-planning-page .menu-card-badges {
        display: flex;
        min-width: 0;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    .menu-planning-page .menu-card-eyebrow {
        margin-bottom: 0.75rem;
    }

    .menu-planning-page .menu-card-code,
    .menu-planning-page .menu-card-date,
    .menu-planning-page .menu-card-category,
    .menu-planning-page .menu-status-badge {
        display: inline-flex;
        max-width: 100%;
        align-items: center;
        gap: 0.375rem;
        border-radius: 0.625rem;
        padding: 0.375rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.25rem;
        white-space: nowrap;
    }

    .menu-planning-page .menu-card-code {
        background: #F8FAFC;
        color: #334155;
        border: 1px solid #E2E8F0;
        font-variant-numeric: tabular-nums;
    }

    .menu-planning-page .menu-card-date {
        background: #F8FAFC;
        color: #334155;
        border: 1px solid #E2E8F0;
    }

    .menu-planning-page .menu-card-category {
        background: #F1F5F9;
        color: #334155;
        border: 1px solid #E2E8F0;
    }

    .menu-planning-page .menu-status-badge--success {
        background: #DCFCE7;
        color: #166534;
    }

    .menu-planning-page .menu-status-badge--warning {
        background: #FEF3C7;
        color: #92400E;
    }

    .menu-planning-page .menu-status-badge--danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    .menu-planning-page .menu-status-badge--info {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .menu-planning-page .menu-card-title {
        display: -webkit-box;
        margin: 0;
        overflow: hidden;
        color: #0F172A;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.3;
        overflow-wrap: anywhere;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .menu-planning-page .menu-card-title--split {
        display: block;
        overflow-wrap: normal;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .menu-planning-page .menu-card-subtitle {
        display: -webkit-box;
        margin: 0.25rem 0 0;
        overflow: hidden;
        color: #475569;
        font-size: 0.875rem;
        line-height: 1.5;
        overflow-wrap: anywhere;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .menu-planning-page .menu-card-badges {
        margin-top: 0.75rem;
    }

    .menu-planning-page .menu-card-meta {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .menu-planning-page .menu-card-meta-item {
        min-width: 0;
    }

    .menu-planning-page .menu-card-meta-label {
        display: block;
        margin-bottom: 0.125rem;
        color: #64748B;
        font-size: 0.8125rem;
    }

    .menu-planning-page .menu-card-meta-value {
        display: block;
        overflow: hidden;
        color: #0F172A;
        font-size: 0.875rem;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .menu-planning-page .menu-card-side {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
        align-items: start;
        gap: 0.75rem;
    }

    .menu-planning-page .menu-card-metric {
        min-width: 0;
        padding-top: 0.125rem;
    }

    .menu-planning-page .menu-card-metric-label {
        display: block;
        color: #64748B;
        font-size: 0.75rem;
        line-height: 1.4;
    }

    .menu-planning-page .menu-card-metric-value {
        display: block;
        margin-top: 0.25rem;
        color: #0F172A;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.25;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .menu-planning-page .menu-card-delete {
        display: inline-flex;
        width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        border: 1px solid #FECACA;
        border-radius: 0.625rem;
        background: #FFFFFF;
        color: #B91C1C;
    }

    .menu-planning-page .menu-card-delete:hover {
        background: #FEF2F2;
    }

    .menu-planning-page .menu-card-delete:focus-visible {
        outline: 2px solid rgba(185, 28, 28, 0.45);
        outline-offset: 2px;
    }

    .menu-planning-page .menu-cycle {
        margin: 1.5rem 0;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #E2E8F0;
    }

    .menu-planning-page .menu-cycle-title {
        margin: 0 0 0.75rem;
        color: #334155;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .menu-planning-page .menu-cycle-steps {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.75rem;
    }

    .menu-planning-page .menu-cycle-step {
        display: grid;
        min-width: 0;
        grid-template-columns: 1.75rem minmax(0, 1fr);
        grid-template-rows: auto minmax(2.5rem, auto) auto;
        align-items: start;
        gap: 0.5rem 0.75rem;
        padding: 0.875rem;
        border: 1px solid #E2E8F0;
        border-radius: 0.75rem;
        background: #FFFFFF;
    }

    .menu-planning-page .menu-cycle-icon {
        display: inline-flex;
        width: 1.75rem;
        height: 1.75rem;
        grid-column: 1;
        grid-row: 1;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: #F1F5F9;
        color: #64748B;
        font-size: 0.75rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .menu-planning-page .menu-cycle-icon--done {
        background: #DCFCE7;
        color: #166534;
    }

    .menu-planning-page .menu-cycle-icon--active {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .menu-planning-page .menu-cycle-icon--attention {
        background: #FEE2E2;
        color: #991B1B;
    }

    .menu-planning-page .menu-cycle-copy {
        min-width: 0;
        grid-column: 2;
        grid-row: 1 / 3;
    }

    .menu-planning-page .menu-cycle-step-title {
        display: block;
        color: #0F172A;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .menu-planning-page .menu-cycle-step-description {
        display: -webkit-box;
        margin-top: 0.25rem;
        overflow: hidden;
        color: #475569;
        font-size: 0.75rem;
        line-height: 1.45;
        overflow-wrap: anywhere;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .menu-planning-page .menu-cycle-action-slot {
        display: flex;
        min-height: 40px;
        grid-column: 1 / -1;
        grid-row: 3;
        align-items: end;
    }

    .menu-planning-page .menu-cycle-action {
        min-height: 40px !important;
        padding: 0.5rem 0.875rem;
        border-radius: 0.625rem;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .menu-planning-page .menu-cycle-action--secondary {
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        color: #334155;
    }

    .menu-planning-page .menu-cycle-action--secondary:hover {
        background: #F8FAFC;
    }

    .menu-planning-page .menu-nutrition-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.75rem;
        margin: 1.5rem 0;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #E2E8F0;
    }

    .menu-planning-page .menu-nutrition-card {
        min-width: 0;
        padding: 1rem;
        border: 1px solid #E2E8F0;
        border-radius: 0.875rem;
        background: #FFFFFF;
    }

    .menu-planning-page .menu-nutrition-label {
        color: #475569;
        font-size: 0.8125rem;
        font-weight: 500;
    }

    .menu-planning-page .menu-nutrition-value {
        margin: 0.375rem 0 0;
        color: #0F172A;
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.2;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .menu-planning-page .menu-nutrition-unit {
        color: #64748B;
        font-size: 0.875rem;
        font-weight: 400;
    }

    .menu-planning-page .menu-ingredients {
        margin-top: 1.5rem;
        border: 1px solid #E2E8F0;
        border-radius: 0.875rem;
        background: #FFFFFF;
        overflow: hidden;
    }

    .menu-planning-page .menu-ingredients-heading {
        padding: 1rem 1rem 0;
    }

    .menu-planning-page .menu-ingredients-title {
        margin: 0;
        color: #334155;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .menu-planning-page .menu-ingredients-subtitle {
        margin-top: 0.125rem;
        color: #64748B;
        font-size: 0.8125rem;
    }

    .menu-planning-page .menu-ingredients-table-wrap {
        max-height: 22rem;
        overflow: auto;
    }

    .menu-planning-page .menu-ingredients-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .menu-planning-page .menu-ingredients-table th {
        position: sticky;
        top: 0;
        z-index: 1;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
        color: #475569;
        font-size: 0.8125rem;
        font-weight: 600;
    }

    .menu-planning-page .menu-ingredients-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #F1F5F9;
        color: #334155;
        font-size: 0.8125rem;
    }

    .menu-planning-page .menu-ingredients-table tr:last-child td {
        border-bottom: 0;
    }

    .menu-planning-page .menu-ingredients-table th:nth-child(n+2),
    .menu-planning-page .menu-ingredients-table td:nth-child(n+2) {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    @media (min-width: 640px) {
        .menu-planning-page .planning-stages {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .menu-planning-page .planning-status-legend {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .menu-planning-page .menu-filter-form {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .menu-planning-page .menu-card-meta {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .menu-planning-page .menu-cycle-steps {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .menu-planning-page .menu-nutrition-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1280px) {
        .menu-planning-page .planning-stages {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .menu-planning-page .planning-stage-card:not(:nth-child(4))::after {
            position: absolute;
            z-index: 1;
            top: 2.75rem;
            right: -1.125rem;
            width: 1rem;
            color: #64748B;
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1rem;
            text-align: center;
            content: "";
        }

        .menu-planning-page .planning-stage-card:not(:nth-child(4))::after {
            content: "›";
        }

        .menu-planning-page .planning-status-legend {
            grid-template-columns: minmax(0, 1fr);
        }

        .menu-planning-page .menu-filter-form {
            grid-template-columns: minmax(170px, 2fr) minmax(140px, 1.35fr) minmax(130px, 1.2fr) minmax(145px, 1.3fr) minmax(132px, 1.15fr) 4rem;
        }

        .menu-planning-page .menu-card-header {
            grid-template-columns: minmax(0, 1fr) minmax(22rem, 0.9fr);
            align-items: start;
        }

        .menu-planning-page .menu-nutrition-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }

    @media (min-width: 768px) and (max-width: 1279px) {
        .menu-planning-page .menu-card-header {
            grid-template-columns: minmax(0, 1fr) minmax(20rem, 0.8fr);
            align-items: start;
        }

        .menu-planning-page .menu-nutrition-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) and (max-width: 1279px) {
        .menu-planning-page .menu-filter-form {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 640px) and (max-width: 767px) {
        .menu-planning-page .menu-nutrition-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 639px) {
        .menu-planning-page .planning-stage-header {
            align-items: flex-start;
            flex-direction: column;
            gap: 1rem;
        }

        .menu-planning-page .planning-stage-header-action {
            width: 100%;
        }

        .menu-planning-page .planning-stage-card:not(:nth-child(4))::after {
            position: absolute;
            right: auto;
            bottom: -1rem;
            left: 2.625rem;
            height: 1rem;
            border-left: 2px solid #CBD5E1;
            content: "";
        }

        .menu-planning-page .planning-nutrition-chart {
            min-height: 16rem;
        }

        .menu-planning-page .menu-filter-card,
        .menu-planning-page .menu-detail-card {
            padding: 1rem;
        }

        .menu-planning-page .menu-card-side {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .menu-planning-page .menu-card-delete {
            width: 100%;
            grid-column: 1 / -1;
        }

        .menu-planning-page .menu-card-meta {
            grid-template-columns: minmax(0, 1fr);
        }

        .menu-planning-page .menu-cycle-steps {
            grid-template-columns: minmax(0, 1fr);
        }

        .menu-planning-page .menu-cycle-action {
            width: 100%;
        }

        .menu-planning-page .menu-nutrition-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .menu-planning-page .menu-ingredients-table thead {
            display: none;
        }

        .menu-planning-page .menu-ingredients-table tbody tr {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            padding: 0.625rem 0.875rem;
            border-bottom: 1px solid #E2E8F0;
        }

        .menu-planning-page .menu-ingredients-table tbody tr:last-child {
            border-bottom: 0;
        }

        .menu-planning-page .menu-ingredients-table td {
            padding: 0.25rem;
            border: 0;
            text-align: left !important;
        }

        .menu-planning-page .menu-ingredients-table td:first-child {
            grid-column: 1 / -1;
            color: #0F172A;
            font-weight: 600;
        }

        .menu-planning-page .menu-ingredients-table td:nth-child(2)::before {
            display: block;
            margin-bottom: 0.125rem;
            color: #64748B;
            content: "Per porsi";
            font-size: 0.6875rem;
            font-variant-numeric: normal;
        }

        .menu-planning-page .menu-ingredients-table td:nth-child(3)::before {
            display: block;
            margin-bottom: 0.125rem;
            color: #64748B;
            content: "Total kebutuhan";
            font-size: 0.6875rem;
            font-variant-numeric: normal;
        }
    }
</style>
@endpush

@section('content')
<div class="menu-planning-page" x-data="{
    openCreateModal: false,
    openPaguModal: false,
    openApproveModal: false,
    activeMenuId: null,
    activeMenuCode: '',
    activeMenuName: '',
    activeCost: 0,
    operationalDate: '{{ $defaultOperationalDate ?? now()->format('Y-m-d') }}',
    menuCode: '{{ $nextMenuCode ?? 'MNU-SS1-' . now()->format('Ymd') . '-01' }}',
    isGeneratingCode: false,
    async updateMenuCode() {
        if (!this.operationalDate) return;
        this.isGeneratingCode = true;
        try {
            const res = await fetch(`{{ route('menu-planning.generate-code') }}?date=${encodeURIComponent(this.operationalDate)}`);
            const data = await res.json();
            if (data.menu_code) {
                this.menuCode = data.menu_code;
            }
        } catch (e) {
            const clean = this.operationalDate.replace(/-/g, '');
            this.menuCode = `MNU-SS1-${clean}-01`;
        } finally {
            this.isGeneratingCode = false;
        }
    },
    items: [{ ingredient_id: '', qty_per_portion: '', preparation_notes: '' }],
    addItem() {
        this.items.push({ ingredient_id: '', qty_per_portion: '', preparation_notes: '' });
    },
    removeItem(idx) {
        if (this.items.length > 1) {
            this.items.splice(idx, 1);
        }
    },
    openPagu(id, code, name, cost) {
        this.activeMenuId = id;
        this.activeMenuCode = code;
        this.activeMenuName = name;
        this.activeCost = cost;
        this.openPaguModal = true;
    },
    openApprove(id, code, name) {
        this.activeMenuId = id;
        this.activeMenuCode = code;
        this.activeMenuName = name;
        this.openApproveModal = true;
    }
}">

    <header class="planning-stage-header">
        <div>
            <h2 class="planning-stage-header-title">Alur validasi menu</h2>
            <p class="planning-stage-header-subtitle">Ahli Gizi → Akuntan SPPG → Kepala Dapur → Siap produksi</p>
        </div>
        @if(in_array(auth()->user()->role->value, ['admin', 'ahli_gizi']))
            <button type="button" @click="openCreateModal = true" class="planning-stage-header-action">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajukan menu baru
            </button>
        @endif
    </header>

    <div class="planning-stages">
        <article class="surface-card planning-stage-card">
            <div class="planning-stage-heading">
                <span class="planning-stage-marker" aria-hidden="true">1</span>
                <h2 class="planning-stage-title">Susun menu & gizi</h2>
            </div>
            <p class="planning-stage-role"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M12 7a4 4 0 11-8 0 4 4 0 018 0zm8 4a4 4 0 00-3-3.87"/></svg>Ahli Gizi</p>
            <p class="planning-stage-description" title="Susun menu harian dan tetapkan target porsi.">Susun menu harian dan tetapkan target porsi.</p>
            <div class="planning-stage-status">
                <span class="planning-stage-status-label">Status draf</span>
                <span class="planning-stage-status-value {{ ($statusCounts['draft'] ?? 0) > 0 ? 'planning-stage-status-value--info' : '' }}" title="{{ number_format($statusCounts['draft'] ?? 0, 0, ',', '.') }} menu"><span class="planning-stage-status-number">{{ number_format($statusCounts['draft'] ?? 0, 0, ',', '.') }}</span> <span class="planning-stage-status-unit">menu</span></span>
                <span class="planning-stage-status-badge">{{ ($statusCounts['draft'] ?? 0) > 0 ? 'Ada' : 'Kosong' }}</span>
            </div>
        </article>

        <article class="surface-card planning-stage-card">
            <div class="planning-stage-heading">
                <span class="planning-stage-marker" aria-hidden="true">2</span>
                <h2 class="planning-stage-title">Verifikasi PAGU</h2>
            </div>
            <p class="planning-stage-role"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M12 7a4 4 0 11-8 0 4 4 0 018 0zm8 4a4 4 0 00-3-3.87"/></svg>Akuntan SPPG</p>
            <p class="planning-stage-description" title="Batas PAGU: Rp {{ number_format($paguLimit, 0, ',', '.') }} per porsi.">Batas PAGU: <span class="whitespace-nowrap">Rp&nbsp;{{ number_format($paguLimit, 0, ',', '.') }}</span> per porsi.</p>
            <div class="planning-stage-status">
                <span class="planning-stage-status-label" title="Menunggu PAGU">Menunggu PAGU</span>
                <span class="planning-stage-status-value {{ ($statusCounts['pending_budget_review'] ?? 0) > 0 ? 'planning-stage-status-value--attention' : '' }}" title="{{ number_format($statusCounts['pending_budget_review'] ?? 0, 0, ',', '.') }} menu menunggu PAGU"><span class="planning-stage-status-number">{{ number_format($statusCounts['pending_budget_review'] ?? 0, 0, ',', '.') }}</span> <span class="planning-stage-status-unit">menu</span></span>
                <span class="planning-stage-status-badge">{{ ($statusCounts['pending_budget_review'] ?? 0) > 0 ? 'Perlu ditinjau' : 'Kosong' }}</span>
            </div>
        </article>

        <article class="surface-card planning-stage-card">
            <div class="planning-stage-heading">
                <span class="planning-stage-marker" aria-hidden="true">3</span>
                <h2 class="planning-stage-title">Persetujuan dapur</h2>
            </div>
            <p class="planning-stage-role"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M12 7a4 4 0 11-8 0 4 4 0 018 0zm8 4a4 4 0 00-3-3.87"/></svg>Kepala Dapur</p>
            <p class="planning-stage-description" title="Setujui menu atau minta revisi kepada Ahli Gizi.">Setujui menu atau minta revisi.</p>
            <div class="planning-stage-status">
                <span class="planning-stage-status-label" title="Menunggu persetujuan"><span class="planning-stage-status-label-long">Menunggu persetujuan</span><span class="planning-stage-status-label-short">Menunggu</span></span>
                <span class="planning-stage-status-value {{ ($statusCounts['pending_approval'] ?? 0) > 0 ? 'planning-stage-status-value--attention' : '' }}" title="{{ number_format($statusCounts['pending_approval'] ?? 0, 0, ',', '.') }} menu menunggu persetujuan"><span class="planning-stage-status-number">{{ number_format($statusCounts['pending_approval'] ?? 0, 0, ',', '.') }}</span> <span class="planning-stage-status-unit">menu</span></span>
                <span class="planning-stage-status-badge">{{ ($statusCounts['pending_approval'] ?? 0) > 0 ? 'Perlu disetujui' : 'Kosong' }}</span>
            </div>
        </article>

        <article class="surface-card planning-stage-card">
            <div class="planning-stage-heading">
                <span class="planning-stage-marker planning-stage-marker--ready" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                </span>
                <h2 class="planning-stage-title">Siap produksi</h2>
            </div>
            <p class="planning-stage-role">Hasil akhir</p>
            <p class="planning-stage-description" title="Menu disetujui dan siap diproduksi.">Menu disetujui dan siap diproduksi.</p>
            <div class="planning-stage-status">
                <span class="planning-stage-status-label">Disetujui</span>
                <span class="planning-stage-status-value {{ ($statusCounts['approved'] ?? 0) > 0 ? 'planning-stage-status-value--success' : '' }}" title="{{ number_format($statusCounts['approved'] ?? 0, 0, ',', '.') }} menu disetujui"><span class="planning-stage-status-number">{{ number_format($statusCounts['approved'] ?? 0, 0, ',', '.') }}</span> <span class="planning-stage-status-unit">menu</span></span>
                <span class="planning-stage-status-badge {{ ($statusCounts['approved'] ?? 0) > 0 ? 'planning-stage-status-badge--ready' : '' }}">{{ ($statusCounts['approved'] ?? 0) > 0 ? 'Siap' : 'Kosong' }}</span>
            </div>
        </article>
    </div>

    <!-- 2 Grafik Analitik Menu -->
    @php
        $approvalChartValues = [
            $statusCounts['pending_budget_review'] ?? 0,
            $statusCounts['pending_approval'] ?? 0,
            $statusCounts['approved'] ?? 0,
            $statusCounts['in_production'] ?? 0,
            $statusCounts['revision_needed'] ?? 0,
        ];
        $approvalChartLabels = ['Review PAGU', 'Persetujuan Dapur', 'Disetujui', 'Produksi', 'Minta Revisi'];
        $approvalChartColors = ['#1D4ED8', '#D97706', '#15803D', '#061F47', '#B91C1C'];
        $approvalChartTotal = array_sum($approvalChartValues);
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-6">
        <section class="surface-card planning-chart-card lg:col-span-8 p-5 sm:p-6">
            <div class="mb-4">
                <h2 class="font-display font-extrabold text-base text-slate-900">
                    {{ $chartLabels->count() <= 1 ? 'Energi kalori & protein per menu' : 'Tren energi kalori & protein per menu' }}
                </h2>
                <p class="planning-chart-subtitle mt-1">Perbandingan kandungan gizi pada menu yang ditampilkan.</p>
            </div>
            <div class="planning-nutrition-chart">
                <canvas id="menuNutritionChart"></canvas>
            </div>
        </section>

        <section class="surface-card planning-chart-card lg:col-span-4 p-5 sm:p-6">
            <div class="mb-4">
                <h2 class="font-display font-extrabold text-base text-slate-900">Status alur persetujuan</h2>
                <p class="planning-chart-subtitle mt-1">Jumlah menu pada setiap tahap.</p>
            </div>
            <div class="planning-status-chart">
                <canvas id="menuStatusChart"></canvas>
            </div>
            <div class="planning-status-legend" aria-label="Rincian jumlah status menu">
                @foreach($approvalChartLabels as $index => $label)
                    <div class="planning-status-legend-item {{ $approvalChartValues[$index] === 0 ? 'is-zero' : '' }}">
                        <span class="planning-status-legend-name">
                            <span class="planning-status-legend-swatch" style="background-color: {{ $approvalChartColors[$index] }}"></span>
                            <span>{{ $label }}</span>
                        </span>
                        <span class="planning-status-legend-count">{{ number_format($approvalChartValues[$index], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Filter & Search Bar Lengkap -->
    <div class="surface-card menu-filter-card mb-6">
        <form method="GET" action="{{ route('menu-planning.index') }}" class="menu-filter-form">
            <div>
                <label for="menu-filter-search" class="menu-filter-label">Cari menu atau kode</label>
                <div class="menu-filter-search">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input id="menu-filter-search" type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau kode menu"
                           class="menu-filter-control">
                </div>
            </div>

            <div>
                <label for="menu-filter-status" class="menu-filter-label">Status alur</label>
                <select id="menu-filter-status" name="status" class="menu-filter-control">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua status</option>
                    <option value="pending_budget_review" {{ $status === 'pending_budget_review' ? 'selected' : '' }}>Review PAGU (Akuntan)</option>
                    <option value="pending_approval" {{ $status === 'pending_approval' ? 'selected' : '' }}>Menunggu Persetujuan (Kepala Dapur)</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="in_production" {{ $status === 'in_production' ? 'selected' : '' }}>Sedang Diproduksi</option>
                    <option value="revision_needed" {{ $status === 'revision_needed' ? 'selected' : '' }}>Minta Revisi</option>
                </select>
            </div>

            <div>
                <label for="menu-filter-pagu" class="menu-filter-label">Status PAGU</label>
                <select id="menu-filter-pagu" name="pagu_status" class="menu-filter-control">
                    <option value="all" {{ $paguStatus === 'all' ? 'selected' : '' }}>Semua PAGU</option>
                    <option value="sesuai_pagu" {{ $paguStatus === 'sesuai_pagu' ? 'selected' : '' }}>Sesuai PAGU (&le; Rp{{ number_format($paguLimit, 0, ',', '.') }})</option>
                    <option value="melebihi_pagu" {{ $paguStatus === 'melebihi_pagu' ? 'selected' : '' }}>Melebihi PAGU (&gt; Rp{{ number_format($paguLimit, 0, ',', '.') }})</option>
                    <option value="pending" {{ $paguStatus === 'pending' ? 'selected' : '' }}>Belum Diverifikasi</option>
                </select>
            </div>

            <div>
                <label for="menu-filter-category" class="menu-filter-label">Kategori penerima</label>
                <select id="menu-filter-category" name="recipient_category" class="menu-filter-control">
                    <option value="all" {{ $recipientCategory === 'all' ? 'selected' : '' }}>Semua kategori</option>
                    <option value="siswa" {{ $recipientCategory === 'siswa' ? 'selected' : '' }}>Siswa (Sekolah & Pesantren)</option>
                    <option value="balita_bumil" {{ $recipientCategory === 'balita_bumil' ? 'selected' : '' }}>Ibu Hamil & Balita</option>
                    <option value="balita_busui" {{ $recipientCategory === 'balita_busui' ? 'selected' : '' }}>Ibu Menyusui & Balita</option>
                </select>
            </div>

            <div>
                <label for="menu-filter-date" class="menu-filter-label">Dari tanggal</label>
                <input id="menu-filter-date" type="date" name="date_from" value="{{ $dateFrom }}" class="menu-filter-control">
            </div>

            <div>
                <button type="submit" class="menu-filter-submit">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>

                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Siklus Menu Harian -->
    @if($menus->isEmpty())
        <div class="surface-card rounded-3xl p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="font-display font-extrabold text-lg text-slate-900">Belum Ada Jadwal Menu yang Cocok</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-5">
                Silakan ubah filter pencarian atau susun jadwal menu gizi baru untuk SPPG Sungai Selayur 1.
            </p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($menus as $menu)
                @php
                    $formatDisplayNumber = static function ($value, $precision = 3) {
                        $formatted = number_format((float) $value, $precision, ',', '.');
                        return rtrim(rtrim($formatted, '0'), ',');
                    };
                    $menuNameParts = explode(':', $menu->menu_name, 2);
                    $menuTitle = trim($menuNameParts[0]);
                    $menuComponents = isset($menuNameParts[1])
                        ? collect(explode(',', $menuNameParts[1]))
                            ->map(static fn ($component) => \Illuminate\Support\Str::ucfirst(trim($component)))
                            ->filter()
                            ->implode(' · ')
                        : null;
                    $recipientLabel = match($menu->recipient_category ?? 'siswa') {
                        'balita_bumil' => 'Ibu hamil & balita',
                        'balita_busui' => 'Ibu menyusui & balita',
                        'gabungan' => 'Gabungan',
                        default => 'Siswa',
                    };
                    $workflowBadgeClass = match($menu->status) {
                        'approved', 'completed' => 'menu-status-badge--success',
                        'in_production' => 'menu-status-badge--info',
                        'revision_needed' => 'menu-status-badge--danger',
                        'pending_approval' => 'menu-status-badge--warning',
                        default => 'menu-status-badge--info',
                    };
                    $paguBadgeClass = match($menu->pagu_status) {
                        'sesuai_pagu' => 'menu-status-badge--success',
                        'melebihi_pagu' => 'menu-status-badge--danger',
                        default => 'menu-status-badge--warning',
                    };
                    $paguBadgeLabel = match($menu->pagu_status) {
                        'sesuai_pagu' => 'Sesuai PAGU',
                        'melebihi_pagu' => 'Melebihi PAGU',
                        default => 'PAGU menunggu review',
                    };
                @endphp
                <article class="surface-card menu-detail-card">
                    <header class="menu-card-header">
                        <div class="menu-card-identity">
                            <div class="menu-card-eyebrow">
                                <span class="menu-card-code">{{ $menu->menu_code }}</span>
                                <span class="menu-card-date">{{ $menu->operational_date->translatedFormat('l, d M Y') }}</span>
                                <span class="menu-card-category">{{ $recipientLabel }}</span>
                            </div>
                            @if($menuComponents !== null)
                                <h3 class="menu-card-title menu-card-title--split" title="{{ $menu->menu_name }}">{{ $menuTitle }}</h3>
                                @if($menuComponents !== '')
                                    <p class="menu-card-subtitle" title="{{ $menuComponents }}">{{ $menuComponents }}</p>
                                @endif
                            @else
                                <h3 class="menu-card-title" title="{{ $menu->menu_name }}">{{ $menu->menu_name }}</h3>
                            @endif
                            <div class="menu-card-badges">
                                <span class="menu-status-badge {{ $paguBadgeClass }}">{{ $paguBadgeLabel }}</span>
                                <span class="menu-status-badge {{ $workflowBadgeClass }}">{{ $menu->status_label }}</span>
                            </div>
                            <div class="menu-card-meta">
                                <div class="menu-card-meta-item">
                                    <span class="menu-card-meta-label">Disusun oleh</span>
                                    <span class="menu-card-meta-value" title="{{ $menu->nutritionist?->name ?? 'Belum tercatat' }}">{{ $menu->nutritionist?->name ?? 'Belum tercatat' }}</span>
                                </div>
                                <div class="menu-card-meta-item">
                                    <span class="menu-card-meta-label">PAGU diverifikasi</span>
                                    <span class="menu-card-meta-value" title="{{ $menu->accountant?->name ?? 'Belum tercatat' }}">{{ $menu->accountant?->name ?? 'Belum tercatat' }}</span>
                                </div>
                                <div class="menu-card-meta-item">
                                    <span class="menu-card-meta-label">Disetujui oleh</span>
                                    <span class="menu-card-meta-value" title="{{ $menu->headOfKitchen?->name ?? 'Belum tercatat' }}">{{ $menu->headOfKitchen?->name ?? 'Belum tercatat' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="menu-card-side">
                            <div class="menu-card-metric">
                                <span class="menu-card-metric-label">Target pemorsian</span>
                                <span class="menu-card-metric-value">{{ number_format($menu->target_portions, 0, ',', '.') }} <span class="text-sm font-medium text-slate-500">porsi</span></span>
                            </div>
                            <div class="menu-card-metric">
                                <span class="menu-card-metric-label">Estimasi biaya/porsi</span>
                                <span class="menu-card-metric-value"><span class="whitespace-nowrap">Rp&nbsp;{{ number_format($menu->estimated_cost_per_portion, 0, ',', '.') }}</span></span>
                            </div>

                            @if(in_array(auth()->user()->role->value, ['admin', 'ahli_gizi']))
                                <form method="POST" action="{{ route('menu-planning.destroy', $menu) }}" onsubmit="return confirm('Hapus jadwal menu {{ $menu->menu_code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="menu-card-delete" title="Hapus menu" aria-label="Hapus menu {{ $menu->menu_code }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </header>

                    <section class="menu-cycle">
                        <h4 class="menu-cycle-title">Progres siklus validasi multi-tahap (SOP BGN)</h4>
                        <div class="menu-cycle-steps">
                            <div class="menu-cycle-step">
                                <span class="menu-cycle-icon menu-cycle-icon--done" aria-label="Selesai">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.25 7.25a1 1 0 01-1.415 0l-3.75-3.75a1 1 0 011.415-1.415l3.042 3.043 6.543-6.543a1 1 0 011.415 0z" clip-rule="evenodd"/></svg>
                                </span>
                                <div class="menu-cycle-copy">
                                    <span class="menu-cycle-step-title">Draf menu Ahli Gizi</span>
                                    <span class="menu-cycle-step-description" title="Tersusun lengkap">Tersusun lengkap</span>
                                </div>
                                <div class="menu-cycle-action-slot" aria-hidden="true"></div>
                            </div>

                            @php
                                $paguStepDone = $menu->pagu_status === 'sesuai_pagu';
                                $paguStepAttention = $menu->pagu_status === 'melebihi_pagu';
                                $approvalStepDone = in_array($menu->status, ['approved', 'in_production', 'completed']);
                                $approvalStepAttention = $menu->status === 'revision_needed';
                                $approvalStepActive = $menu->status === 'pending_approval';
                            @endphp
                            <div class="menu-cycle-step">
                                <span class="menu-cycle-icon {{ $paguStepDone ? 'menu-cycle-icon--done' : ($paguStepAttention ? 'menu-cycle-icon--attention' : 'menu-cycle-icon--active') }}" aria-label="{{ $paguStepDone ? 'Selesai' : ($paguStepAttention ? 'Perlu perhatian' : 'Menunggu aksi') }}">
                                    @if($paguStepDone)
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.25 7.25a1 1 0 01-1.415 0l-3.75-3.75a1 1 0 011.415-1.415l3.042 3.043 6.543-6.543a1 1 0 011.415 0z" clip-rule="evenodd"/></svg>
                                    @elseif($paguStepAttention)
                                        !
                                    @else
                                        2
                                    @endif
                                </span>
                                <div class="menu-cycle-copy">
                                    <span class="menu-cycle-step-title">Validasi PAGU Akuntan</span>
                                    <span class="menu-cycle-step-description" title="{{ $menu->pagu_notes ?? 'Menunggu pemeriksaan' }}">{{ $menu->pagu_notes ?? 'Menunggu pemeriksaan' }}</span>
                                </div>
                                <div class="menu-cycle-action-slot">
                                    @if(in_array(auth()->user()->role->value, ['admin', 'akuntan']))
                                        <button
                                            type="button"
                                            @click="openPagu({{ $menu->id }}, '{{ $menu->menu_code }}', '{{ addslashes($menu->menu_name) }}', {{ $menu->estimated_cost_per_portion }})"
                                            class="menu-cycle-action {{ ($menu->pagu_status ?? 'pending') === 'pending' ? 'bg-status-info text-white hover:bg-blue-800' : 'menu-cycle-action--secondary' }} cursor-pointer"
                                        >
                                            Validasi
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="menu-cycle-step">
                                <span class="menu-cycle-icon {{ $approvalStepDone ? 'menu-cycle-icon--done' : ($approvalStepAttention ? 'menu-cycle-icon--attention' : ($approvalStepActive ? 'menu-cycle-icon--active' : '')) }}" aria-label="{{ $approvalStepDone ? 'Selesai' : ($approvalStepAttention ? 'Perlu revisi' : ($approvalStepActive ? 'Menunggu aksi' : 'Belum dimulai')) }}">
                                    @if($approvalStepDone)
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.25 7.25a1 1 0 01-1.415 0l-3.75-3.75a1 1 0 011.415-1.415l3.042 3.043 6.543-6.543a1 1 0 011.415 0z" clip-rule="evenodd"/></svg>
                                    @elseif($approvalStepAttention)
                                        !
                                    @else
                                        3
                                    @endif
                                </span>
                                <div class="menu-cycle-copy">
                                    <span class="menu-cycle-step-title">Persetujuan Kepala Dapur</span>
                                    <span class="menu-cycle-step-description" title="{{ $menu->approval_notes ?? 'Menunggu PAGU selesai' }}">{{ $menu->approval_notes ?? 'Menunggu PAGU selesai' }}</span>
                                </div>
                                <div class="menu-cycle-action-slot">
                                    @if(in_array(auth()->user()->role->value, ['admin', 'kepala_dapur']))
                                        <button
                                            type="button"
                                            @click="openApprove({{ $menu->id }}, '{{ $menu->menu_code }}', '{{ addslashes($menu->menu_name) }}')"
                                            class="menu-cycle-action {{ $approvalStepActive ? 'bg-status-info text-white hover:bg-blue-800' : 'menu-cycle-action--secondary' }} cursor-pointer"
                                        >
                                            Keputusan
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="menu-nutrition-grid" aria-label="Ringkasan nutrisi per porsi">
                        <div class="menu-nutrition-card">
                            <span class="menu-nutrition-label">Energi</span>
                            <p class="menu-nutrition-value">{{ $formatDisplayNumber($menu->calories_kcal, 1) }} <span class="menu-nutrition-unit">kkal</span></p>
                        </div>
                        <div class="menu-nutrition-card">
                            <span class="menu-nutrition-label">Protein</span>
                            <p class="menu-nutrition-value">{{ $formatDisplayNumber($menu->protein_gram, 1) }} <span class="menu-nutrition-unit">g</span></p>
                        </div>
                        <div class="menu-nutrition-card">
                            <span class="menu-nutrition-label">Karbohidrat</span>
                            <p class="menu-nutrition-value">{{ $formatDisplayNumber($menu->carbs_gram, 1) }} <span class="menu-nutrition-unit">g</span></p>
                        </div>
                        <div class="menu-nutrition-card">
                            <span class="menu-nutrition-label">Lemak total</span>
                            <p class="menu-nutrition-value">{{ $formatDisplayNumber($menu->fat_gram, 1) }} <span class="menu-nutrition-unit">g</span></p>
                        </div>
                        <div class="menu-nutrition-card">
                            <span class="menu-nutrition-label">Serat pangan</span>
                            <p class="menu-nutrition-value">{{ $formatDisplayNumber($menu->fiber_gram, 1) }} <span class="menu-nutrition-unit">g</span></p>
                        </div>
                    </section>

                    @if($menu->items->isNotEmpty())
                        <section class="menu-ingredients" aria-labelledby="menu-ingredients-title-{{ $menu->id }}">
                            <div class="menu-ingredients-heading">
                                <h4 id="menu-ingredients-title-{{ $menu->id }}" class="menu-ingredients-title">Komposisi bahan baku</h4>
                                <p class="menu-ingredients-subtitle">Gramasi standar porsi MBG</p>
                            </div>
                            <div class="menu-ingredients-table-wrap">
                                <table class="menu-ingredients-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Bahan</th>
                                            <th scope="col">Per porsi</th>
                                            <th scope="col">Total kebutuhan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($menu->items as $item)
                                            <tr>
                                                <td>{{ $item->ingredient?->name ?? 'Bahan' }}</td>
                                                <td>{{ $formatDisplayNumber($item->qty_per_portion) }} {{ $item->ingredient?->unit }}</td>
                                                <td>{{ $formatDisplayNumber($item->total_planned_qty) }} {{ $item->ingredient?->unit }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    <!-- MODAL 1: SUSUN JADWAL MENU BARU (AHLI GIZI) -->
    <template x-teleport="body">
        <div x-show="openCreateModal" x-cloak class="menu-planning-dialog fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75 overflow-y-auto">
            <div @click.away="openCreateModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-auto max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-700">Ahli Gizi</span>
                        <h3 class="font-display font-extrabold text-lg text-slate-900">Susun Rencana Menu Harian Baru</h3>
                    </div>
                    <button @click="openCreateModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 cursor-pointer">✕</button>
                </div>

                <form method="POST" action="{{ route('menu-planning.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Operasional</label>
                            <input type="date" name="operational_date" required x-model="operationalDate" @change="updateMenuCode()"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>Kode Menu</span>
                                <span class="text-[10px] font-extrabold uppercase text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Otomatis</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="menu_code" :value="menuCode" readonly
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100/90 font-mono font-extrabold text-xs text-emerald-800 cursor-not-allowed select-all focus:outline-none">
                                <div x-show="isGeneratingCode" class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Dibuat otomatis oleh sistem SPPG</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Target Porsi (Total)</label>
                            <input type="number" name="target_portions" required min="10" value="1414"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Penerima Manfaat</label>
                            <select name="recipient_category" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                                <option value="siswa">Siswa (5 Sekolah & Pesantren)</option>
                                <option value="balita_bumil">Ibu Hamil & Balita (Posyandu Melati)</option>
                                <option value="balita_busui">Ibu Menyusui & Balita (Posyandu Mawar)</option>
                                <option value="gabungan">Gabungan (Seluruh 1.414 Penerima)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Biaya Bahan per Porsi (Rp)</label>
                            <input type="number" name="estimated_cost_per_portion" required value="14850"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                            <span class="text-[10px] text-slate-400">Batas PAGU saat ini: Rp {{ number_format($paguLimit, 0, ',', '.') }} / porsi</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Paket Menu Lengkap</label>
                        <input type="text" name="menu_name" required placeholder="Cth: Paket Gizi Nusantara: Nasi Pulen, Ayam Madu, Tempe Bacem, Sayur Buncis & Susu"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold">
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Energi (kkal)</label>
                            <input type="number" step="0.1" name="calories_kcal" required value="685"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Protein (g)</label>
                            <input type="number" step="0.1" name="protein_gram" required value="28.5"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Karbohidrat (g)</label>
                            <input type="number" step="0.1" name="carbs_gram" required value="84.0"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Lemak (g)</label>
                            <input type="number" step="0.1" name="fat_gram" required value="21.0"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Serat (g)</label>
                            <input type="number" step="0.1" name="fiber_gram" value="8.2"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                        </div>
                    </div>

                    <!-- Komposisi Bahan Baku -->
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-slate-700">Komposisi Bahan Baku (Gramasi Porsi)</label>
                            <button type="button" @click="addItem()" class="text-xs font-bold text-emerald-600 hover:underline cursor-pointer">+ Tambah Bahan</button>
                        </div>
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="grid grid-cols-12 gap-2 mb-2">
                                <div class="col-span-6">
                                    <select :name="'items['+idx+'][ingredient_id]'" x-model="item.ingredient_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach($ingredients as $ing)
                                            <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-4">
                                    <input type="number" step="0.001" :name="'items['+idx+'][qty_per_portion]'" x-model="item.qty_per_portion" placeholder="Qty/Porsi"
                                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                                </div>
                                <div class="col-span-2 flex items-center justify-center">
                                    <button type="button" @click="removeItem(idx)" class="text-xs text-rose-500 font-bold hover:underline cursor-pointer">Hapus</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="openCreateModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                            Ajukan Menu ke Akuntan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: VERIFIKASI PAGU ANGGARAN (AKUNTAN SPPG) -->
    <template x-teleport="body">
        <div x-show="openPaguModal" x-cloak class="menu-planning-dialog fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75">
            <div @click.away="openPaguModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-sky-700">Akuntan SPPG</span>
                        <h3 class="font-display font-extrabold text-base text-slate-900">Verifikasi Kesesuaian PAGU Anggaran</h3>
                    </div>
                    <button @click="openPaguModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 cursor-pointer">✕</button>
                </div>

                <div class="p-3.5 rounded-2xl bg-sky-50 border border-sky-200 mb-4 text-xs">
                    <p class="font-bold text-sky-950" x-text="activeMenuCode + ' • ' + activeMenuName"></p>
                    <div class="mt-2 flex items-center justify-between text-[11px]">
                        <span class="text-sky-800">Estimasi Biaya per Porsi:</span>
                        <span class="font-mono font-extrabold text-sky-950" x-text="'Rp ' + Number(activeCost).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[11px]">
                        <span class="text-sky-800">Batas PAGU Resmi BGN:</span>
                        <span class="font-mono font-bold text-sky-950">Rp {{ number_format($paguLimit, 0, ',', '.') }} / Porsi</span>
                    </div>
                </div>

                <form method="POST" :action="'{{ url('menu-planning') }}/' + activeMenuId + '/verify-pagu'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Hasil Pemeriksaan PAGU</label>
                        <select name="pagu_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                            <option value="sesuai_pagu">Sesuai PAGU (biaya tidak melebihi batas; diteruskan ke Kepala Dapur)</option>
                            <option value="melebihi_pagu">Melebihi PAGU (perlu ditinjau sebelum diteruskan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Akuntansi & Rekomendasi</label>
                        <textarea name="pagu_notes" rows="3" required placeholder="Tuliskan catatan hasil audit biaya bahan baku terhadap pagu anggaran..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="openPaguModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                            Simpan Hasil Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 3: KEPUTUSAN APPROVAL KEPALA DAPUR -->
    <template x-teleport="body">
        <div x-show="openApproveModal" x-cloak class="menu-planning-dialog fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/75">
            <div @click.away="openApproveModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-700">Kepala Dapur</span>
                        <h3 class="font-display font-extrabold text-base text-slate-900">Persetujuan Rencana Menu</h3>
                    </div>
                    <button @click="openApproveModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 cursor-pointer">✕</button>
                </div>

                <p class="text-xs font-bold text-slate-800 mb-3" x-text="activeMenuCode + ' — ' + activeMenuName"></p>

                <form method="POST" :action="'{{ url('menu-planning') }}/' + activeMenuId + '/approve'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keputusan Kepala Dapur</label>
                        <select name="decision" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                            <option value="approved">Setujui dan jadwalkan produksi</option>
                            <option value="in_production">Setujui dan mulai produksi</option>
                            <option value="revision_needed">Minta revisi kepada Ahli Gizi</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Instruksi Operasional Dapur</label>
                        <textarea name="approval_notes" rows="3" placeholder="Instruksi khusus pemorsian ompreng atau persiapan bumbu..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs">Disetujui sesuai standar operasional dapur SPPG Sungai Selayur 1.</textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="openApproveModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                            Sahkan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Chart 1: Gizi
        const ctxGizi = document.getElementById('menuNutritionChart');
        if (ctxGizi) {
            new Chart(ctxGizi, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [
                        {
                            label: 'Energi Kalori (kkal)',
                            data: {!! json_encode($chartCalories) !!},
                            backgroundColor: 'rgba(21, 128, 61, 0.78)',
                            borderColor: '#15803D',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            maxBarThickness: 48,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Protein (g)',
                            data: {!! json_encode($chartProteins) !!},
                            backgroundColor: 'rgba(29, 78, 216, 0.78)',
                            borderColor: '#1D4ED8',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            maxBarThickness: 48,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    datasets: { bar: { categoryPercentage: 0.45, barPercentage: 0.9, grouped: true } },
                    onResize(chart, size) {
                        chart.options.datasets.bar.categoryPercentage = Math.min(0.55, 136 / size.width);
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#475569',
                                usePointStyle: true,
                                pointStyle: 'circle',
                                pointStyleWidth: 10,
                                boxWidth: 10,
                                boxHeight: 10,
                                padding: 16,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#FFFFFF',
                            titleColor: '#061F47',
                            bodyColor: '#334155',
                            borderColor: '#CBD5E1',
                            borderWidth: 1,
                            padding: 10,
                            titleFont: { weight: '600' },
                            bodyFont: { weight: '500' },
                            displayColors: true,
                            boxPadding: 4
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#475569',
                                maxRotation: 0,
                                minRotation: 0,
                                font: { size: 11 }
                            },
                            grid: { display: false }
                        },
                        y: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'kkal',
                                color: '#15803D',
                                font: { size: 12, weight: '600' }
                            },
                            ticks: { color: '#475569', font: { size: 11 } },
                            grid: { color: '#E2E8F0' }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'gram',
                                color: '#1D4ED8',
                                font: { size: 12, weight: '600' }
                            },
                            ticks: { color: '#475569', font: { size: 11 } },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        }

        // Chart 2: Status
        const ctxStatus = document.getElementById('menuStatusChart');
        if (ctxStatus) {
            const centerTotal = {
                id: 'approvalChartCenterTotal',
                afterDraw(chart) {
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return;
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2;
                    const fontFamily = getComputedStyle(chart.canvas).fontFamily || 'sans-serif';
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#061F47';
                    ctx.font = `700 24px ${fontFamily}`;
                    ctx.fillText('{{ number_format($approvalChartTotal, 0, ',', '.') }}', centerX, centerY - 7);
                    ctx.fillStyle = '#64748B';
                    ctx.font = `500 12px ${fontFamily}`;
                    ctx.fillText('menu', centerX, centerY + 13);
                    ctx.restore();
                }
            };
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($approvalChartLabels) !!},
                    datasets: [{
                        data: {!! json_encode($approvalChartValues) !!},
                        backgroundColor: {!! json_encode($approvalChartColors) !!},
                        borderWidth: 0,
                        spacing: 0,
                        hoverOffset: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#FFFFFF',
                            titleColor: '#061F47',
                            bodyColor: '#334155',
                            borderColor: '#CBD5E1',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: true,
                            boxPadding: 4
                        }
                    }
                },
                plugins: [centerTotal]
            });
        }
    });
</script>
@endpush
@endsection
