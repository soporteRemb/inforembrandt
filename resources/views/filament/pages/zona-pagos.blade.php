<x-filament-panels::page>
    <style>

        .zp-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }

        .zp-title svg {
            width: 29px;
            height: 29px;
            color: #b91c1c;
            stroke-width: 1.8;
        }
        .zp-page {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #111827;
        }


        .zp-title::before {
            content: "";
            position: absolute;
        }

        .zp-title-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #b91c1c;
        }

        .zp-title-icon svg {
            width: 22px;
            height: 22px;
        }

        /*
        |--------------------------------------------------------------------------
        | DISTRIBUCIÓN GENERAL
        |--------------------------------------------------------------------------
        */

        .zp-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            grid-template-areas:
                "encabezado spacer"
                "obligaciones resumen"
                "recientes resumen";
            column-gap: 20px;
            row-gap: 12px;
            align-items: start;
        }

        .zp-obligaciones {
            grid-area: obligaciones;
            min-width: 0;
        }

        .zp-summary {
            grid-area: resumen;
        }

        .zp-recent {
            grid-area: recientes;
        }
        .zp-section-heading-grid {
            grid-area: encabezado;
            margin-bottom: 0;
        }

        .zp-summary-spacer {
            grid-area: spacer;
        }

        /*
        |--------------------------------------------------------------------------
        | TARJETAS
        |--------------------------------------------------------------------------
        */

        .zp-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow:
                0 1px 2px rgba(15, 23, 42, .04),
                0 4px 14px rgba(15, 23, 42, .035);
        }

        .zp-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: -10px;
            margin-bottom: 14px;
        }

        /*
        |--------------------------------------------------------------------------
        | AVISO
        |--------------------------------------------------------------------------
        */

        .zp-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            margin-bottom: 18px;
            background: #fff7f7;
            border: 1px solid #f5cccc;
            border-left: 4px solid #c5161d;
            border-radius: 9px;
            color: #4b5563;
            font-size: 13px;
        }

        .zp-info svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            color: #374151;
        }

        /*
        |--------------------------------------------------------------------------
        | ENCABEZADOS
        |--------------------------------------------------------------------------
        */

        .zp-section-heading {
            margin-bottom: 12px;
        }

        .zp-section-title {
            font-size: 17px;
            font-weight: 750;
            color: #111827;
        }

        .zp-section-description {
            margin-top: 3px;
            color: #6b7280;
            font-size: 13px;
        }

        /*
        |--------------------------------------------------------------------------
        | ESTUDIANTE
        |--------------------------------------------------------------------------
        */

        .zp-student {
            overflow: hidden;
            margin-bottom: 14px;
        }

        .zp-student-header {
            min-height: 52px;
            padding: 11px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #f0fdf4;
            border-bottom: 1px solid #bbf7d0;
        }
        .zp-student-header {
            background: linear-gradient(
                180deg,
                #f0fdf4 0%,
                #ecfdf5 100%
            );
        }

        .zp-student-name {
            display: flex;
            align-items: center;
            min-width: 0;
            gap: 10px;
            font-size: 15px;
            font-weight: 700;
        }

        .zp-student-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #bbf7d0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #15803d;
        }

        .zp-student-icon svg {
            width: 19px;
            height: 19px;
        }

        .zp-student-title {
            color: #166534;
        }

        .zp-course {
            color: #64748b;
            font-weight: 500;
            font-size: 13px;
        }

        /*
        |--------------------------------------------------------------------------
        | OBLIGACIONES
        |--------------------------------------------------------------------------
        */

        .zp-obligation-head,
        .zp-obligation-row {
            display: grid;
            grid-template-columns:
                36px
                minmax(190px, 1.7fr)
                minmax(140px, 1fr)
                120px;
        }

        .zp-obligation-head {
            display: grid;
            grid-template-columns:
                28px
                minmax(220px, 1.4fr)
                minmax(180px, 1fr)
                130px;
            gap: 12px;
            align-items: center;
            padding: 9px 14px;
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            background: #fafafa;
            border-bottom: 1px solid #e5e7eb;
        }

        .zp-obligation-row {
            min-height: 58px;
            padding: 9px 14px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .zp-obligation-row:last-child {
            border-bottom: none;
        }

        .zp-obligation-row:hover {
            background: #f9fafb;
        }

        .zp-obligation-row:has(.zp-checkbox:checked) {
            background: #fff7f7;
        }

        .zp-obligation-row.zp-disabled {
            background: #fafafa;
            color: #64748b;
        }

        .zp-concept {
            font-weight: 700;
            color: #111827;
        }

        .zp-money {
            font-weight: 750;
            white-space: nowrap;
            text-align: right;
        }

        /*
        |--------------------------------------------------------------------------
        | CHECKBOX ROJO
        |--------------------------------------------------------------------------
        */

        .zp-checkbox-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .zp-checkbox {
            appearance: none !important;
            -webkit-appearance: none !important;
            width: 18px;
            height: 18px;
            margin: 0;
            border: 1.5px solid #9ca3af;
            border-radius: 3px;
            background: #ffffff;
            cursor: pointer;
            display: inline-grid;
            place-content: center;
        }

        .zp-checkbox:checked {
            background-color: #c5161d;
            border-color: #c5161d;
        }
        .zp-checkbox:focus,
        .zp-checkbox:focus-visible {
            outline: none !important;
            box-shadow: 0 0 0 2px rgba(197, 22, 29, .18) !important;
            border-color: #c5161d !important;
        }

        .zp-checkbox:checked:focus,
        .zp-checkbox:checked:focus-visible {
            background-color: #c5161d !important;
            border-color: #c5161d !important;
            box-shadow: 0 0 0 2px rgba(197, 22, 29, .18) !important;
        }

        .zp-checkbox:checked::after {
            content: "";
            width: 9px;
            height: 5px;
            border-left: 2px solid #ffffff;
            border-bottom: 2px solid #ffffff;
            transform: rotate(-45deg) translateY(-1px);
        }

        .zp-checkbox:disabled {
            cursor: not-allowed;
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        /*
        |--------------------------------------------------------------------------
        | ESTADO DIGITAL
        |--------------------------------------------------------------------------
        */

        .zp-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 3px 7px;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }

        .zp-badge-process {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .zp-empty {
            padding: 34px 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }

        /*
        |--------------------------------------------------------------------------
        | RESUMEN
        |--------------------------------------------------------------------------
        */

        .zp-summary {
            position: sticky;
            top: 20px;
            padding: 16px;
            border-top: 3px solid #b91c1c;
        }

        .zp-summary-title {
            font-size: 16px;
            font-weight: 750;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .zp-summary-title svg {
            width: 19px;
            height: 19px;
            color: #b91c1c;
        }

        .zp-summary-student {
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 11px 12px;
            margin-bottom: 9px;
        }

        .zp-summary-student-name {
            font-size: 13px;
            font-weight: 700;
            color: #b91c1c;
            margin-bottom: 8px;
        }

        .zp-summary-course {
            color: #64748b;
            font-weight: 500;
        }

        .zp-summary-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 4px 0;
            font-size: 12px;
        }

        .zp-summary-line-name {
            color: #374151;
            min-width: 0;
        }

        .zp-summary-line-value {
            font-weight: 700;
            white-space: nowrap;
        }

        .zp-summary-empty {
            border: 1px dashed #d1d5db;
            border-radius: 9px;
            padding: 20px 12px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .zp-divider {
            border-top: 1px solid #e5e7eb;
            margin: 13px 0;
        }

        .zp-total-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 4px 0;
            font-size: 13px;
            font-weight: 700;
        }

        .zp-count {
            color: #c5161d;
            font-size: 18px;
        }

        .zp-total {
            color: #c5161d;
            font-size: 21px;
            font-weight: 800;
        }

        /*
        |--------------------------------------------------------------------------
        | MÉTODO DE PAGO
        |--------------------------------------------------------------------------
        */

        .zp-method-label {
            display: block;
            margin: 14px 0 6px;
            font-size: 13px;
            font-weight: 700;
        }

        .zp-select {
            width: 100%;
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0 40px 0 12px;
            background-color: #ffffff !important;
            color: #111827;
            font-size: 14px;
            outline: none;

            appearance: none !important;
            -webkit-appearance: none !important;

            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;

            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 16px 16px !important;
        }

        /*
        |--------------------------------------------------------------------------
        | BOTÓN
        |--------------------------------------------------------------------------
        */

        .zp-pay-button {
            width: 100%;
            min-height: 43px;
            margin-top: 16px;
            border: 0;
            border-radius: 8px;
            background: #c5161d;
            color: #ffffff;
            font-weight: 750;
            font-size: 14px;
            cursor: pointer;
            transition: .18s;
        }

        .zp-pay-button:hover:not(:disabled) {
            background: #a91218;
        }

        .zp-pay-button:disabled {
            opacity: .48;
            cursor: not-allowed;
        }

        /*
        |--------------------------------------------------------------------------
        | SEGURIDAD
        |--------------------------------------------------------------------------
        */

        .zp-secure {
            margin-top: 14px;
            padding: 11px 12px;
            display: flex;
            gap: 9px;
            align-items: center;
            background: #fafafa;
            border-radius: 8px;
            color: #6b7280;
            font-size: 11px;
        }

        .zp-secure svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            color: #475569;
        }

        /*
        |--------------------------------------------------------------------------
        | PAGOS RECIENTES
        |--------------------------------------------------------------------------
        */

        .zp-recent {
            overflow: hidden;
        }

        .zp-recent-header {
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .zp-recent-title {
            font-size: 15px;
            font-weight: 750;
        }

        .zp-see-all {
            border: 0;
            background: transparent;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .zp-see-all:hover {
            text-decoration: underline;
        }

        .zp-table-scroll {
            overflow-x: auto;
        }

        .zp-recent-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .zp-recent-table th {
            padding: 9px 12px;
            text-align: left;
            color: #475569;
            background: #fafafa;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 700;
            white-space: nowrap;
        }

        .zp-recent-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .zp-status {
            display: inline-flex;
            padding: 3px 7px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
        }

        .zp-status-aprobada {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .zp-status-pendiente,
        .zp-status-procesando {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .zp-status-rechazada,
        .zp-status-cancelada,
        .zp-status-expirada {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }


        .zp-payment-clickable {
            cursor: pointer;
        }

        .zp-recent-table tr.zp-payment-clickable td {
            transition: background-color .15s ease;
        }

        .zp-recent-table tr.zp-payment-clickable:hover td {
            background: #f9fafb;
        }

        .zp-payment-card.zp-payment-clickable {
            transition: background-color .15s ease;
        }

        .zp-payment-card.zp-payment-clickable:hover {
            background: #f9fafb;
        }

        /*
        |--------------------------------------------------------------------------
        | MODAL HISTORIAL
        |--------------------------------------------------------------------------
        */

        .zp-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, .48);
        }

        .zp-modal {
            width: min(950px, 100%);
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .22);
            overflow: hidden;
        }

        .zp-modal-header {
            padding: 16px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .zp-modal-title {
            font-size: 17px;
            font-weight: 750;
        }

        .zp-modal-close {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            color: #475569;
            cursor: pointer;
        }

        .zp-modal-close svg {
            width: 19px;
            height: 19px;
        }

        .zp-modal-body {
            overflow: auto;
        }

        /*
        |--------------------------------------------------------------------------
        | MODAL DETALLE DEL PAGO
        |--------------------------------------------------------------------------
        */

        .zp-detail-modal {
            width: min(760px, 100%);
        }

        .zp-detail-reference {
            margin-top: 3px;
            font-size: 12px;
            color: #64748b;
        }

        .zp-detail-body {
            padding: 18px;
        }

        .zp-detail-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }

        .zp-detail-summary-item {
            padding: 11px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            background: #fafafa;
        }

        .zp-detail-summary-label {
            margin-bottom: 5px;
            font-size: 11px;
            color: #64748b;
        }

        .zp-detail-summary-value {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }

        .zp-detail-total {
            font-size: 17px;
            font-weight: 800;
            color: #b91c1c;
        }

        .zp-detail-section-title {
            margin-bottom: 9px;
            font-size: 14px;
            font-weight: 750;
            color: #111827;
        }

        .zp-detail-list {
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .zp-detail-item {
            padding: 12px 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        .zp-detail-item:last-child {
            border-bottom: none;
        }

        .zp-detail-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }

        .zp-detail-student {
            margin-bottom: 3px;
            font-size: 13px;
            font-weight: 700;
            color: #166534;
        }

        .zp-detail-concept {
            font-size: 13px;
            color: #374151;
        }

        .zp-detail-item-value {
            flex-shrink: 0;
            font-size: 14px;
            font-weight: 750;
            color: #111827;
        }

        .zp-detail-pending {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 16px;
            padding: 12px 13px;
            border: 1px solid #fde68a;
            border-radius: 9px;
            background: #fffbeb;
            color: #92400e;
            font-size: 12px;
            line-height: 1.45;
        }

        .zp-detail-pending svg {
            width: 19px;
            height: 19px;
            flex-shrink: 0;
        }

        @media (max-width: 700px) {
            .zp-detail-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .zp-detail-body {
                padding: 14px;
            }
        }
        /*
        |--------------------------------------------------------------------------
        | TARJETAS MÓVILES DE PAGOS
        |--------------------------------------------------------------------------
        */

        .zp-recent-mobile,
        .zp-history-mobile {
            display: none;
        }

        .zp-recent-desktop,
        .zp-history-desktop {
            display: block;
        }

        .zp-payment-card {
            padding: 12px 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        .zp-payment-card:last-child {
            border-bottom: none;
        }

        .zp-payment-card-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .zp-payment-card-date {
            font-size: 12px;
            color: #64748b;
        }

        .zp-payment-card-total {
            font-size: 15px;
            font-weight: 800;
            color: #111827;
            white-space: nowrap;
        }

        .zp-payment-card-reference {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 7px;
            word-break: break-word;
        }

        .zp-payment-card-row {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 3px 0;
            font-size: 13px;
        }

        .zp-payment-card-label {
            color: #64748b;
        }

        .zp-payment-card-value {
            color: #111827;
            text-align: right;
            font-weight: 500;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1200px) {
            .zp-layout {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "encabezado"
                    "obligaciones"
                    "resumen"
                    "recientes";
                row-gap: 12px;
            }

            .zp-summary-spacer {
                display: none;
            }

            .zp-summary {
                position: static;
            }
        }
        @media (max-width: 850px) {
            .zp-obligation-head {
                display: none;
            }

            .zp-obligation-row {
                grid-template-columns:
                    28px
                    minmax(0, 1fr)
                    auto;
                gap: 8px 10px;
                align-items: start;
                font-size: 14px;
                min-height: 64px;
            }

            .zp-checkbox-wrap {
                grid-column: 1;
                grid-row: 1;
                padding-top: 2px;
            }

            .zp-concept-cell {
                grid-column: 2;
                grid-row: 1;
            }

            .zp-value-cell {
                grid-column: 3;
                grid-row: 1;
            }

            .zp-period-cell {
                grid-column: 2 / 4;
                grid-row: 2;
                font-size: 12px;
                line-height: 1.45;
                color: #6b7280;
            }

            .zp-student-header {
                align-items: flex-start;
            }

            .zp-title {
                font-size: 24px;
            }

            .zp-section-title {
                font-size: 18px;
            }

            .zp-section-description {
                font-size: 13px;
                line-height: 1.45;
            }

            .zp-student-title {
                font-size: 15px;
            }

            .zp-course {
                font-size: 13px;
            }

            .zp-concept {
                font-size: 14px;
            }

            .zp-money {
                font-size: 14px;
            }

            .zp-summary-title {
                font-size: 17px;
            }

            .zp-summary-student-name {
                font-size: 14px;
            }

            .zp-summary-line {
                font-size: 13px;
                line-height: 1.45;
            }

            .zp-total-line {
                font-size: 14px;
            }

            .zp-method-label {
                font-size: 14px;
            }

            .zp-select {
                font-size: 15px;
                height: 44px;
            }

            .zp-pay-button {
                min-height: 46px;
                font-size: 14px;
            }

            .zp-secure {
                font-size: 12px;
                line-height: 1.45;
            }

            .zp-checkbox {
                width: 21px;
                height: 21px;
            }

            /*
            |--------------------------------------------------------------------------
            | PAGOS RECIENTES E HISTORIAL EN CELULAR
            |--------------------------------------------------------------------------
            */

            .zp-recent-desktop,
            .zp-history-desktop {
                display: none;
            }

            .zp-recent-mobile,
            .zp-history-mobile {
                display: block;
            }

            .zp-table-scroll {
                overflow-x: visible;
            }

            .zp-recent-table {
                min-width: 0;
            }

            /*
            |--------------------------------------------------------------------------
            | MODAL EN CELULAR
            |--------------------------------------------------------------------------
            */

            .zp-modal-backdrop {
                padding: 10px;
            }

            .zp-modal {
                width: 100%;
                max-height: 92vh;
            }

            .zp-modal-body {
                overflow-y: auto;
                overflow-x: hidden;
            }

            /*
            |--------------------------------------------------------------------------
            | TARJETAS DE PAGO EN CELULAR
            |--------------------------------------------------------------------------
            */

            .zp-payment-card {
                padding: 14px 16px;
            }

            .zp-payment-card-top {
                align-items: center;
            }

            .zp-payment-card-date {
                font-size: 12px;
                line-height: 1.4;
            }

            .zp-payment-card-total {
                font-size: 16px;
            }

            .zp-payment-card-reference {
                font-size: 13px;
                line-height: 1.4;
                margin-bottom: 9px;
            }

            .zp-payment-card-row {
                font-size: 13px;
                line-height: 1.4;
                padding: 4px 0;
            }

            .zp-payment-card-label {
                flex-shrink: 0;
            }

            .zp-payment-card-value {
                min-width: 0;
                overflow-wrap: anywhere;
            }
        }
    </style>

    <div class="zp-page">

        <div class="zp-title">
            <x-heroicon-o-credit-card />

            <span>Pagos</span>
        </div>

        <div class="zp-info">
            <x-heroicon-o-information-circle />

            <div>
                La obligación se marcará como pagada únicamente cuando el colegio reciba la confirmación del pago.
            </div>
        </div>

        <div class="zp-layout">

            {{-- OBLIGACIONES --}}
            <div class="zp-section-heading zp-section-heading-grid">
                <div class="zp-section-title">
                    Obligaciones pendientes
                </div>

                <div class="zp-section-description">
                    Selecciona las obligaciones que deseas pagar. Puedes elegir una o varias.
                </div>
            </div>

            <div class="zp-summary-spacer"></div>

            <div class="zp-obligaciones">

                @forelse($estudiantes as $estudiante)

                    <div class="zp-card zp-student">

                        <div class="zp-student-header">

                            <div class="zp-student-name">

                                <div class="zp-student-icon">
                                    <x-heroicon-o-user />
                                </div>

                                <div>
                                    <span class="zp-student-title">
                                        {{ $estudiante['nombre_completo'] }}
                                    </span>

                                    <span class="zp-course">
                                        · {{ $estudiante['curso'] ?? '-' }}
                                    </span>
                                </div>

                            </div>

                            <div class="text-xs text-gray-500">
                                {{ $estudiante['cantidad_obligaciones'] ?? 0 }}

                                {{ ($estudiante['cantidad_obligaciones'] ?? 0) === 1
                                    ? 'obligación'
                                    : 'obligaciones'
                                }}
                            </div>

                        </div>

                        @if(! empty($estudiante['obligaciones']))

                            <div class="zp-obligation-head">
                                <div></div>
                                <div>Concepto</div>
                                <div>Periodo / referencia</div>

                                <div style="text-align:right;">
                                    Valor
                                </div>
                            </div>

                            @foreach($estudiante['obligaciones'] as $obligacion)

                                @php
                                    $seleccionable =
                                        (bool) (
                                            $obligacion['seleccionable']
                                            ?? false
                                        );

                                    $enProceso =
                                        ($obligacion['estado_pago'] ?? null)
                                        === 'en_proceso';

                                    $mes =
                                        $obligacion['mes']
                                        ?? null;

                                    $fechaVencimiento =
                                        $obligacion['fecha_vencimiento']
                                        ?? null;
                                @endphp

                                <div
                                    class="
                                        zp-obligation-row
                                        {{ ! $seleccionable ? 'zp-disabled' : '' }}
                                    "
                                >

                                    <div class="zp-checkbox-wrap">

                                        <input
                                            type="checkbox"
                                            value="{{ $obligacion['movimiento_id'] }}"
                                            wire:model.live="seleccionados"
                                            class="zp-checkbox"
                                            @disabled(! $seleccionable)
                                        >

                                    </div>

                                    <div class="zp-concept-cell">

                                        <div class="zp-concept">
                                            {{ $obligacion['concepto'] }}
                                        </div>

                                        @if($enProceso)
                                            <div style="margin-top:5px;">
                                                <span class="zp-badge zp-badge-process">
                                                    Pago digital en proceso
                                                </span>
                                            </div>
                                        @endif

                                    </div>

                                    <div class="zp-period-cell">

                                        @if($mes)
                                            {{ $mes }}
                                        @else
                                            {{ $obligacion['concepto'] }}
                                        @endif

                                        @if(
                                            $enProceso
                                            && ! empty(
                                                $obligacion['orden_referencia']
                                            )
                                        )
                                            <div class="text-xs text-gray-500 mt-1">
                                                Orden {{ $obligacion['orden_referencia'] }}
                                            </div>
                                        @endif

                                    </div>


                                    <div class="zp-money zp-value-cell">
                                        $ {{ number_format(
                                            (float) (
                                                $obligacion['saldo_pendiente']
                                                ?? 0
                                            ),
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </div>

                                </div>

                            @endforeach

                        @else

                            <div class="zp-empty">
                                Este estudiante no tiene obligaciones pendientes.
                            </div>

                        @endif

                    </div>

                @empty

                    <div class="zp-card zp-empty">
                        No se encontraron estudiantes u obligaciones disponibles para esta cuenta familiar.
                    </div>

                @endforelse

            </div>

            {{-- RESUMEN --}}
            <div class="zp-card zp-summary">

                <div class="zp-summary-title">
                    <x-heroicon-o-receipt-percent />
                    <span>Resumen del pago</span>
                </div>

                @forelse($resumenSeleccionado as $estudiante)

                    <div class="zp-summary-student">

                        <div class="zp-summary-student-name">
                            {{ $estudiante['nombre_completo'] }}

                            <span class="zp-summary-course">
                                ({{ $estudiante['curso'] }})
                            </span>
                        </div>

                        @foreach($estudiante['obligaciones'] as $obligacion)

                            <div class="zp-summary-line">

                                <div class="zp-summary-line-name">
                                    {{ $obligacion['concepto'] }}

                                    @if(! empty($obligacion['mes']))
                                        - {{ $obligacion['mes'] }}
                                    @endif
                                </div>

                                <div class="zp-summary-line-value">
                                    $ {{ number_format(
                                        (float) $obligacion['saldo_pendiente'],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                @empty

                    <div class="zp-summary-empty">
                        Selecciona una o varias obligaciones para preparar el pago.
                    </div>

                @endforelse

                <div class="zp-divider"></div>

                <div class="zp-total-line">
                    <span>
                        Conceptos seleccionados
                    </span>

                    <span class="zp-count">
                        {{ $cantidadSeleccionada }}
                    </span>
                </div>

                <div class="zp-total-line">
                    <span>
                        Total a pagar
                    </span>

                    <span class="zp-total">
                        $ {{ number_format(
                            $totalSeleccionado,
                            0,
                            ',',
                            '.'
                        ) }}
                    </span>
                </div>

                {{--
                    Selector de método de pago reservado para integración futura
                    con proveedor de pagos.

                    <div class="zp-method">
                        <label class="zp-method-label">
                            Método de pago
                        </label>

                        <select
                            wire:model="metodoPago"
                            class="zp-select"
                        >
                            <option value="pse">PSE</option>
                        </select>
                    </div>
                <button
                    type="button"
                    wire:click="continuarPago"
                    wire:loading.attr="disabled"
                    wire:target="continuarPago"
                    class="zp-pay-button"
                    @disabled(
                        $cantidadSeleccionada === 0
                    )
                >
                    <span
                        wire:loading.remove
                        wire:target="continuarPago"
                    >
                        Continuar con el pago →
                    </span>

                    <span
                        wire:loading
                        wire:target="continuarPago"
                    >
                        Preparando pago...
                    </span>
                </button>
                --}}


                <button
                    type="button"
                    wire:click="continuarPago"
                    wire:loading.attr="disabled"
                    wire:target="continuarPago"
                    class="zp-pay-button"
                    @disabled($cantidadSeleccionada === 0)
                >
                    <span
                        wire:loading.remove
                        wire:target="continuarPago"
                    >
                        Continuar con el pago →
                    </span>

                    <span
                        wire:loading
                        wire:target="continuarPago"
                    >
                        Preparando pago...
                    </span>
                </button>

                <div class="zp-secure">

                    <x-heroicon-o-lock-closed />

                    <span>
                        Serás dirigido al portal externo de ZonaPagos para realizar el pago.
                    </span>

                </div>

            </div>

            {{-- PAGOS RECIENTES --}}
            <div class="zp-card zp-recent">

                <div class="zp-recent-header">

                    <div class="zp-recent-title">
                        Pagos recientes
                    </div>

                    @if(! empty($pagosRecientes))
                        <button
                            type="button"
                            wire:click="abrirHistorialPagos"
                            class="zp-see-all"
                        >
                            Ver todos
                        </button>
                    @endif

                </div>

                @if(! empty($pagosRecientes))

                    {{-- ESCRITORIO --}}
                    <div class="zp-recent-desktop">

                        <div class="zp-table-scroll">

                            <table class="zp-recent-table">

                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Orden</th>
                                        <th>Estudiantes</th>
                                        <th>Método</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach($pagosRecientes as $pago)

                                        @php
                                            $estado =
                                                $pago['estado']
                                                ?? 'pendiente';

                                            $textoEstado =
                                                match ($estado) {
                                                    'aprobada' =>
                                                        'Pago confirmado',

                                                    'procesando' =>
                                                        'Procesando',

                                                    'pendiente' =>
                                                        'Pendiente',

                                                    'rechazada' =>
                                                        'Rechazado',

                                                    'cancelada' =>
                                                        'Cancelado',

                                                    'expirada' =>
                                                        'Expirado',

                                                    default =>
                                                        ucfirst($estado),
                                                };
                                        @endphp

                                        <tr
                                            wire:click="abrirDetallePago({{ $pago['id'] }})"
                                            class="zp-payment-clickable"
                                        >
                                            <td>
                                                {{ $pago['fecha'] }}
                                            </td>

                                            <td>
                                                {{ $pago['referencia'] }}
                                            </td>

                                            <td>
                                                {{ $pago['estudiantes'] }}
                                            </td>

                                            <td>
                                                {{ $pago['metodo_pago'] }}
                                            </td>

                                            <td style="font-weight:700;">
                                                $ {{ number_format(
                                                    (float) $pago['total'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </td>

                                            <td>
                                                <span
                                                    class="
                                                        zp-status
                                                        zp-status-{{ $estado }}
                                                    "
                                                >
                                                    {{ $textoEstado }}
                                                </span>
                                            </td>
                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                    {{-- CELULAR --}}
                    <div class="zp-recent-mobile">

                        @foreach($pagosRecientes as $pago)

                            @php
                                $estado =
                                    $pago['estado']
                                    ?? 'pendiente';

                                $textoEstado =
                                    match ($estado) {
                                        'aprobada' =>
                                            'Pago confirmado',

                                        'procesando' =>
                                            'Procesando',

                                        'pendiente' =>
                                            'Pendiente',

                                        'rechazada' =>
                                            'Rechazado',

                                        'cancelada' =>
                                            'Cancelado',

                                        'expirada' =>
                                            'Expirado',

                                        default =>
                                            ucfirst($estado),
                                    };
                            @endphp

                            <div
                                class="zp-payment-card zp-payment-clickable"
                                wire:click="abrirDetallePago({{ $pago['id'] }})"
                            >

                                <div class="zp-payment-card-top">

                                    <div class="zp-payment-card-date">
                                        {{ $pago['fecha'] }}
                                    </div>

                                    <div class="zp-payment-card-total">
                                        $ {{ number_format(
                                            (float) $pago['total'],
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </div>

                                </div>

                                <div class="zp-payment-card-reference">
                                    Orden {{ $pago['referencia'] }}
                                </div>

                                <div class="zp-payment-card-row">

                                    <span class="zp-payment-card-label">
                                        Estudiante
                                    </span>

                                    <span class="zp-payment-card-value">
                                        {{ $pago['estudiantes'] }}
                                    </span>

                                </div>

                                <div class="zp-payment-card-row">

                                    <span class="zp-payment-card-label">
                                        Método
                                    </span>

                                    <span class="zp-payment-card-value">
                                        {{ $pago['metodo_pago'] }}
                                    </span>

                                </div>

                                <div class="zp-payment-card-row">

                                    <span class="zp-payment-card-label">
                                        Estado
                                    </span>

                                    <span>
                                        <span
                                            class="
                                                zp-status
                                                zp-status-{{ $estado }}
                                            "
                                        >
                                            {{ $textoEstado }}
                                        </span>
                                    </span>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="zp-empty">
                        Aún no hay pagos digitales registrados.
                    </div>

                @endif

            </div>

        </div>

    </div>











    {{-- MODAL HISTORIAL --}}
    @if($mostrarHistorialPagos)

        <div
            class="zp-modal-backdrop"
            wire:click.self="cerrarHistorialPagos"
        >

            <div class="zp-modal">

                <div class="zp-modal-header">

                    <div class="zp-modal-title">
                        Historial de pagos digitales
                    </div>

                    <button
                        type="button"
                        wire:click="cerrarHistorialPagos"
                        class="zp-modal-close"
                        aria-label="Cerrar"
                    >
                        <x-heroicon-o-x-mark />
                    </button>

                </div>

                <div class="zp-modal-body">

                    @if(! empty($historialPagos))

                        {{-- ESCRITORIO --}}
                        <div class="zp-history-desktop">

                            <div class="zp-table-scroll">

                                <table class="zp-recent-table">

                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Orden</th>
                                            <th>Estudiantes</th>
                                            <th>Método</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach($historialPagos as $pago)

                                            @php
                                                $estado =
                                                    $pago['estado']
                                                    ?? 'pendiente';

                                                $textoEstado =
                                                    match ($estado) {
                                                        'aprobada' =>
                                                            'Pago confirmado',

                                                        'procesando' =>
                                                            'Procesando',

                                                        'pendiente' =>
                                                            'Pendiente',

                                                        'rechazada' =>
                                                            'Rechazado',

                                                        'cancelada' =>
                                                            'Cancelado',

                                                        'expirada' =>
                                                            'Expirado',

                                                        default =>
                                                            ucfirst($estado),
                                                    };
                                            @endphp

                                            <tr
                                                wire:click="abrirDetallePago({{ $pago['id'] }})"
                                                class="zp-payment-clickable"
                                            >

                                                <td>
                                                    {{ $pago['fecha'] }}
                                                </td>

                                                <td>
                                                    {{ $pago['referencia'] }}
                                                </td>

                                                <td>
                                                    {{ $pago['estudiantes'] }}
                                                </td>

                                                <td>
                                                    {{ $pago['metodo_pago'] }}
                                                </td>

                                                <td style="font-weight:700;">
                                                    $ {{ number_format(
                                                        (float) $pago['total'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) }}
                                                </td>

                                                <td>
                                                    <span
                                                        class="
                                                            zp-status
                                                            zp-status-{{ $estado }}
                                                        "
                                                    >
                                                        {{ $textoEstado }}
                                                    </span>
                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                        {{-- CELULAR --}}
                        <div class="zp-history-mobile">

                            @foreach($historialPagos as $pago)

                                @php
                                    $estado =
                                        $pago['estado']
                                        ?? 'pendiente';

                                    $textoEstado =
                                        match ($estado) {
                                            'aprobada' =>
                                                'Pago confirmado',

                                            'procesando' =>
                                                'Procesando',

                                            'pendiente' =>
                                                'Pendiente',

                                            'rechazada' =>
                                                'Rechazado',

                                            'cancelada' =>
                                                'Cancelado',

                                            'expirada' =>
                                                'Expirado',

                                            default =>
                                                ucfirst($estado),
                                        };
                                @endphp

                                <div
                                    class="zp-payment-card zp-payment-clickable"
                                    wire:click="abrirDetallePago({{ $pago['id'] }})"
                                >

                                    <div class="zp-payment-card-top">

                                        <div class="zp-payment-card-date">
                                            {{ $pago['fecha'] }}
                                        </div>

                                        <div class="zp-payment-card-total">
                                            $ {{ number_format(
                                                (float) $pago['total'],
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </div>

                                    </div>

                                    <div class="zp-payment-card-reference">
                                        Orden {{ $pago['referencia'] }}
                                    </div>

                                    <div class="zp-payment-card-row">

                                        <span class="zp-payment-card-label">
                                            Estudiante
                                        </span>

                                        <span class="zp-payment-card-value">
                                            {{ $pago['estudiantes'] }}
                                        </span>

                                    </div>

                                    <div class="zp-payment-card-row">

                                        <span class="zp-payment-card-label">
                                            Método
                                        </span>

                                        <span class="zp-payment-card-value">
                                            {{ $pago['metodo_pago'] }}
                                        </span>

                                    </div>

                                    <div class="zp-payment-card-row">

                                        <span class="zp-payment-card-label">
                                            Estado
                                        </span>

                                        <span>
                                            <span
                                                class="
                                                    zp-status
                                                    zp-status-{{ $estado }}
                                                "
                                            >
                                                {{ $textoEstado }}
                                            </span>
                                        </span>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="zp-empty">
                            No hay pagos digitales registrados.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    @endif











    {{-- MODAL DETALLE DEL PAGO --}}
    @if($mostrarDetallePago)

        <div
            class="zp-modal-backdrop"
            wire:click.self="cerrarDetallePago"
        >

            <div class="zp-modal zp-detail-modal">

                <div class="zp-modal-header">

                    <div>

                        <div class="zp-modal-title">
                            Detalle del pago
                        </div>

                        @if(! empty($detallePago['referencia']))
                            <div class="zp-detail-reference">
                                Orden {{ $detallePago['referencia'] }}
                            </div>
                        @endif

                    </div>

                    <button
                        type="button"
                        wire:click="cerrarDetallePago"
                        class="zp-modal-close"
                        aria-label="Cerrar"
                    >
                        <x-heroicon-o-x-mark />
                    </button>

                </div>

                <div class="zp-modal-body">

                    @php
                        $estadoDetalle =
                            $detallePago['estado']
                            ?? 'pendiente';

                        $textoEstadoDetalle =
                            match ($estadoDetalle) {
                                'aprobada' =>
                                    'Pago confirmado',

                                'procesando' =>
                                    'Procesando',

                                'pendiente' =>
                                    'Pendiente',

                                'rechazada' =>
                                    'Rechazado',

                                'cancelada' =>
                                    'Cancelado',

                                'expirada' =>
                                    'Expirado',

                                default =>
                                    ucfirst($estadoDetalle),
                            };
                    @endphp

                    <div class="zp-detail-body">

                        <div class="zp-detail-summary">

                            <div class="zp-detail-summary-item">

                                <div class="zp-detail-summary-label">
                                    Fecha
                                </div>

                                <div class="zp-detail-summary-value">
                                    {{ $detallePago['fecha'] ?? '-' }}
                                </div>

                            </div>

                            <div class="zp-detail-summary-item">

                                <div class="zp-detail-summary-label">
                                    Método
                                </div>

                                <div class="zp-detail-summary-value">
                                    {{ $detallePago['metodo_pago'] ?? 'PSE' }}
                                </div>

                            </div>

                            <div class="zp-detail-summary-item">

                                <div class="zp-detail-summary-label">
                                    Estado
                                </div>

                                <div class="zp-detail-summary-value">

                                    <span
                                        class="
                                            zp-status
                                            zp-status-{{ $estadoDetalle }}
                                        "
                                    >
                                        {{ $textoEstadoDetalle }}
                                    </span>

                                </div>

                            </div>

                            <div class="zp-detail-summary-item">

                                <div class="zp-detail-summary-label">
                                    Total solicitado
                                </div>

                                <div class="zp-detail-total">
                                    $ {{ number_format(
                                        (float) (
                                            $detallePago['total']
                                            ?? 0
                                        ),
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </div>

                            </div>

                        </div>

                        <div class="zp-detail-section-title">
                            Conceptos seleccionados
                        </div>

                        <div class="zp-detail-list">

                            @forelse(
                                $detallePago['detalles'] ?? []
                                as $detalle
                            )

                                <div class="zp-detail-item">

                                    <div class="zp-detail-item-header">

                                        <div>

                                            <div class="zp-detail-student">
                                                {{ $detalle['estudiante'] }}
                                            </div>

                                            <div class="zp-detail-concept">

                                                {{ $detalle['concepto'] }}

                                                @if(! empty($detalle['mes']))
                                                    · {{ $detalle['mes'] }}
                                                @endif

                                            </div>

                                        </div>

                                        <div class="zp-detail-item-value">

                                            $ {{ number_format(
                                                (float) (
                                                    $detalle['valor_a_pagar']
                                                    ?? 0
                                                ),
                                                0,
                                                ',',
                                                '.'
                                            ) }}

                                        </div>

                                    </div>

                                </div>

                            @empty

                                <div class="zp-empty">
                                    No se encontraron conceptos para esta orden.
                                </div>

                            @endforelse

                        </div>

                        @if(
                            in_array(
                                $estadoDetalle,
                                ['pendiente', 'procesando'],
                                true
                            )
                        )

                            <div class="zp-detail-pending">

                                <x-heroicon-o-clock />

                                <div>

                                    <strong>
                                        Pendiente de validación bancaria
                                    </strong>

                                    <div>
                                        Los valores mostrados corresponden a la intención de pago registrada antes de ingresar al portal de pagos.
                                    </div>

                                </div>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    @endif

</x-filament-panels::page>