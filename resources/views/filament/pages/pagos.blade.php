<x-filament-panels::page>

    
    <style>

        /* =========================================================
        DISTRIBUCIÓN PRINCIPAL DE LA PANTALLA
        ========================================================= */

        /* Registrar pago + cola */
        .pagos-work-grid {
            display: grid;
            grid-template-columns: minmax(620px, 1.75fr) minmax(420px, 1fr);
            gap: 14px;
            align-items: stretch;
        }

        /* Obligaciones + historial */
        .pagos-info-grid{
            display:grid;
            grid-template-columns: 0.78fr 1.42fr;
            gap:14px;
            width:100%;
            grid-column:1 / -1;
            align-items:stretch;
        }

        .pagos-info-grid > .pagos-info-card {
            width: 100%;
            min-width: 0;
        }

        /* Altura y scroll de las tarjetas informativas */
        .pagos-obligations-table-wrap,
        .pagos-history-scroll {
            height: 340px;
            overflow: auto;
        }

        .pagos-agreements-scroll {
            height: 220px;
            overflow-y: auto;
        }

        /* Tarjetas informativas */
        .pagos-info-card {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .pagos-agreements-card {
            width: 100%;
            grid-column: 1 / -1;
        }

        .pagos-placeholder {
            min-height: 180px;
            display: grid;
            place-items: center;
            padding: 20px;
            color: #94a3b8;
            font-size: 13px;
            text-align: center;
        }
        .fi-main {
            max-width: none !important;
        }

        .pagos-page {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }

        .pagos-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 0 2px 2px;
        }

        .pagos-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            line-height: 1.1;
            color: #111827;
        }

        .pagos-header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #64748b;
        }

        .pagos-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pagos-btn {
            min-height: 38px;
            padding: 0 16px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid;
            background: #ffffff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: .16s ease;
        }

        .pagos-btn:hover {
            transform: translateY(-1px);
        }

        .pagos-btn-outline-blue {
            color: #2563eb;
            border-color: #bfdbfe;
        }

        .pagos-btn-outline-blue:hover {
            background: #eff6ff;
        }

        .pagos-btn-outline-dark {
            color: #334155;
            border-color: #dbe2ea;
        }

        .pagos-btn-outline-dark:hover {
            background: #f8fafc;
        }

        .pagos-btn-icon {
            width: 17px;
            height: 17px;
        }

        .pagos-btn-chevron {
            width: 14px;
            height: 14px;
            margin-left: 3px;
        }

        .pagos-card {
            background: #ffffff;
            border: 1px solid #dfe5ec;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
        }

        .pagos-student-card {
            display: grid;
            grid-template-columns: minmax(390px, .85fr) minmax(620px, 1.55fr);
            gap: 14px;
            padding: 12px;
        }

        .pagos-student-left {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-width: 0;
        }

        .pagos-search {
            position: relative;
        }

        .pagos-search-results {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            z-index: 50;
            max-height: 260px;
            overflow-y: auto;
            border: 1px solid #dbe2ea;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
        }

        .pagos-search-result {
            width: 100%;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border: 0;
            border-bottom: 1px solid #edf1f5;
            background: #ffffff;
            text-align: left;
            cursor: pointer;
        }

        .pagos-search-result:last-child {
            border-bottom: 0;
        }

        .pagos-search-result:hover {
            background: #f8fafc;
        }

        .pagos-search-result-main {
            min-width: 0;
        }

        .pagos-search-result-name {
            display: block;
            overflow: hidden;
            color: #1e293b;
            font-size: 13px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pagos-search-result-meta {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 11px;
        }

        .pagos-search-result-course {
            flex: 0 0 auto;
            color: #991b1b;
            font-size: 11px;
            font-weight: 700;
        }

        .pagos-search-empty {
            padding: 12px;
            color: #64748b;
            font-size: 12px;
            text-align: center;
        }

        .pagos-search-clear {
            position: absolute;
            right: 38px;
            top: 50%;
            z-index: 2;
            width: 24px;
            height: 24px;
            padding: 0;
            display: grid;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
        }

        .pagos-search-clear:hover {
            background: #f1f5f9;
            color: #dc2626;
        }

        .pagos-search-clear svg {
            width: 14px;
            height: 14px;
        }

        .pagos-student-placeholder {
            min-height: 58px;
            display: flex;
            align-items: center;
            color: #94a3b8;
            font-size: 12px;
        }

        .pagos-search input {
            width: 100%;
            height: 38px;
            padding: 0 40px 0 12px;
            border: 1px solid #d4dde7;
            border-radius: 7px;
            background: #ffffff;
            font-size: 13px;
            color: #0f172a;
            outline: none;
            transition: .15s ease;
        }

        .pagos-search input:focus {
            border-color: #b91c1c;
            box-shadow: 0 0 0 3px rgba(185, 28, 28, .08);
        }

        .pagos-search input::placeholder {
            color: #94a3b8;
        }

        .pagos-search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            color: #475569;
        }

        .pagos-student-info {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .pagos-avatar {
            width: 58px;
            height: 58px;
            flex: 0 0 58px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #f4e8eb;
            color: #991b1b;
            font-size: 22px;
            font-weight: 800;
        }

        .pagos-student-data {
            min-width: 0;
        }

        .pagos-student-name-row {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .pagos-student-name-row h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pagos-badge {
            flex: 0 0 auto;
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        .pagos-badge-success {
            color: #18794e;
            background: #dff7e9;
            border: 1px solid #b8ebce;
        }

        .pagos-student-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin-top: 7px;
            font-size: 11px;
            color: #475569;
        }

        .pagos-student-meta strong {
            color: #1e293b;
            font-weight: 700;
        }

        .pagos-divider {
            color: #cbd5e1;
        }

        .pagos-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            align-content: stretch;
        }

        .pagos-summary-item {
            min-height: 58px;
            padding: 9px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #e4e9ef;
            border-radius: 9px;
            background: #ffffff;
        }

        .pagos-summary-item span {
            display: block;
            margin-bottom: 2px;
            font-size: 11px;
            color: #475569;
        }

        .pagos-summary-item strong {
            display: block;
            font-size: 16px;
            line-height: 1.15;
            font-weight: 800;
            color: #0f172a;
        }

        .pagos-summary-icon {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border-radius: 9px;
            display: grid;
            place-items: center;
        }

        .pagos-summary-icon svg {
            width: 18px;
            height: 18px;
        }

        .pagos-summary-icon-red {
            color: #dc2626;
            background: #feecec;
        }

        .pagos-summary-icon-orange {
            color: #ea8a00;
            background: #fff2dd;
        }

        .pagos-summary-icon-blue {
            color: #2563eb;
            background: #eaf1ff;
        }

        .pagos-summary-icon-green {
            color: #15803d;
            background: #e3f8ec;
        }

        .pagos-summary-soft-red {
            background: #fff7f7;
        }

        .pagos-summary-soft-green {
            background: #f3fcf7;
        }

        .pagos-summary-net {
            justify-content: flex-start;
            padding-left: 18px;
        }

        .pagos-text-red {
            color: #dc2626 !important;
        }

        .pagos-text-orange {
            color: #e58a00 !important;
        }

        .pagos-text-blue {
            color: #2563eb !important;
        }

        .pagos-text-green {
            color: #15803d !important;
        }

        /* =========================================================
        ZONA OPERATIVA PRINCIPAL
        ========================================================= */

       

        .pagos-panel {
            min-width: 0;
            overflow: hidden;
        }

        .pagos-panel-header {
            min-height: 46px;
            padding: 11px 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px solid #e5eaf0;
        }

        .pagos-panel-title {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #111827;
        }

        .pagos-panel-title span {
            font-weight: 500;
            color: #64748b;
        }

        .pagos-panel-body {
            padding: 10px;
        }

        .pagos-field-grid {
            display: grid;
            gap: 10px;
        }

        .pagos-field-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .pagos-field-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pagos-field {
            min-width: 0;
        }

        .pagos-field label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
        }

        .pagos-required {
            color: #dc2626;
        }

        .pagos-input,
        .pagos-select {
            width: 100%;
            height: 40px;
            padding: 0 10px;
            border: 1px solid #d7dee7;
            border-radius: 6px;
            background: #ffffff;
            color: #1e293b;
            font-size: 14px;
            outline: none;
            transition: .15s ease;
        }

        .pagos-input:focus,
        .pagos-select:focus {
            border-color: #b91c1c;
            box-shadow: 0 0 0 3px rgba(185, 28, 28, .07);
        }

        .pagos-input[readonly] {
            background: #f8fafc;
            color: #475569;
        }

        .pagos-input-money {
            font-weight: 700;
        }

        .pagos-receipt-number {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 16px;
            font-weight: 800;
        }

        .pagos-receipt-number svg {
            width: 14px;
            height: 14px;
            color: #64748b;
        }

        .pagos-subsection-title {
            margin: 14px 0 8px;
            font-size: 11px;
            font-weight: 800;
            color: #1e293b;
        }

        .pagos-payment-methods {
            border: 1px solid #e3e8ef;
            border-radius: 7px;
            overflow: hidden;
        }

        .pagos-payment-methods-header,
        .pagos-payment-method-row {
            display: grid;
            grid-template-columns:
                minmax(90px, 1.1fr)
                minmax(72px, .8fr)
                minmax(90px, 1fr)
                minmax(100px, 1fr)
                32px;
            gap: 6px;
            align-items: center;
        }

        .pagos-payment-methods-header {
            padding: 7px 8px;
            background: #f8fafc;
            border-bottom: 1px solid #e5eaf0;
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
        }

        .pagos-payment-method-row {
            padding: 6px 8px;
            border-bottom: 1px solid #eef2f6;
        }

        .pagos-payment-method-row:last-child {
            border-bottom: 0;
        }

        .pagos-mini-input,
        .pagos-mini-select {
            width: 100%;
            height: 34px;
            padding: 0 7px;
            border: 1px solid #d8e0e8;
            border-radius: 5px;
            background: #ffffff;
            font-size: 11px;
            color: #334155;
            outline: none;
        }

        .pagos-icon-button {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-grid;
            place-items: center;
            border: 0;
            border-radius: 5px;
            background: transparent;
            cursor: pointer;
        }

        .pagos-icon-button svg {
            width: 14px;
            height: 14px;
        }

        .pagos-icon-button-danger {
            color: #dc2626;
        }

        .pagos-icon-button-edit {
            color: #334155;
        }

        .pagos-icon-button:hover {
            background: #f1f5f9;
        }

        .pagos-method-footer {
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-top: 1px solid #e5eaf0;
            background: #fbfcfd;
        }

        .pagos-add-method {
            height: 28px;
            padding: 0 9px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .pagos-add-method svg {
            width: 13px;
            height: 13px;
        }

        .pagos-method-total {
            font-size: 10px;
            color: #475569;
        }

        .pagos-method-total strong {
            margin-left: 5px;
            color: #15803d;
            font-size: 13px;
        }

        .pagos-register-footer {
            margin-top: 12px;
            padding-top: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-top: 1px solid #eef2f6;
        }

        .pagos-received-info {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: #64748b;
        }

        .pagos-received-info strong {
            color: #334155;
        }

        .pagos-received-info svg {
            width: 13px;
            height: 13px;
        }

        .pagos-btn-primary {
            min-height: 34px;
            padding: 0 13px;
            border: 1px solid #b91c1c;
            border-radius: 6px;
            background: #c81e1e;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .pagos-btn-primary:hover {
            background: #a91818;
        }

        


       /* =========================================================
        REGISTRAR PAGO - NUEVA DISTRIBUCIÓN COMPACTA
        ========================================================= */

        .pagos-panel-body {
            padding: 10px;
        }

        .pagos-register-layout {
            display: grid;
            grid-template-columns: minmax(420px, 1.05fr) minmax(400px, .95fr);
            gap: 12px;
            align-items: stretch;
            height: 100%;
        }

        .pagos-work-grid > .pagos-panel {
            height: 100%;
        }

        .pagos-work-grid > .pagos-panel > .pagos-panel-body {
            height: calc(100% - 46px);
        }

        .pagos-register-column {
            height: 100%;
        }

        .pagos-register-column-payment,
        .pagos-register-column-info {
            display: flex;
            flex-direction: column;
        }
        .pagos-register-column {
            min-width: 0;
            padding: 12px;
            border: 1px solid #e3e8ef;
            border-radius: 9px;
            background: #ffffff;
        }

        .pagos-register-column-payment {
            display: flex;
            flex-direction: column;
            gap: 0;
            justify-content: space-between;
        }

        .pagos-register-column-info {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .pagos-register-column-info .pagos-transaction-summary {
            margin-top: auto;
        }

        .pagos-register-layout .pagos-field {
            min-width: 0;
        }

        .pagos-register-layout .pagos-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            line-height: 1.2;
            font-weight: 700;
            color: #475569;
        }

        .pagos-register-layout .pagos-input,
        .pagos-register-layout .pagos-select {
            width: 100%;
            height: 38px;
            padding: 0 10px;
            border: 1px solid #d7dee7;
            border-radius: 6px;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 13px;
            outline: none;
            transition:
                border-color .15s ease,
                box-shadow .15s ease;
        }

        .pagos-register-layout .pagos-input:focus,
        .pagos-register-layout .pagos-select:focus {
            border-color: #b91c1c;
            box-shadow: 0 0 0 3px rgba(185, 28, 28, .07);
        }

        .pagos-register-layout .pagos-input::placeholder {
            color: #94a3b8;
        }

        .pagos-register-layout .pagos-input-money {
            font-weight: 800;
        }

        .pagos-register-column-payment > .pagos-field:first-child {
            margin-top: 2px;
        }

        .pagos-register-column-info .pagos-transaction-summary {
            margin-top: auto;
        }

        .pagos-register-column-info .pagos-btn-add-queue {
            margin-top: 0;
        }


        /* =========================================================
        SELECTS CON FLECHA NATIVA
        ========================================================= */

        .pagos-register-layout .pagos-select {
            appearance: auto !important;
            -webkit-appearance: menulist !important;
            -moz-appearance: auto !important;

            background-image: none !important;
            padding-right: 8px;
        }


        /* =========================================================
        VALOR Y DESCUENTO
        ========================================================= */

        .pagos-payment-values-grid {
            display: grid;
            grid-template-columns: minmax(150px, .85fr) minmax(120px, .65fr);
            gap: 14px;
            width: 72%;
            margin-top: 2px;
        }


        /* =========================================================
        FORMA DE PAGO, REFERENCIA Y FECHA
        ========================================================= */

        .pagos-payment-data-grid {
            display: grid;
            grid-template-columns:
                minmax(140px, 1fr)
                minmax(140px, 1fr)
                minmax(120px, .75fr);
            gap: 14px;
            align-items: end;
            margin-top: 4px;
        }


        /* =========================================================
        RECIBIDO POR Y RECIBÍ DE
        ========================================================= */

        .pagos-receipt-person-grid {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 14px;
            align-items: end;
            width: 100%;
        }

        .pagos-receipt-person-grid > * {
            min-width: 0;
        }

        .pagos-receipt-person-grid .pagos-field,
        .pagos-receipt-person-grid .pagos-input {
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .pagos-register-column,
        .pagos-register-column * {
            box-sizing: border-box;
        }

        .pagos-register-column-info {
            overflow: hidden;
        }

        .pagos-detail-field {
            margin-top: 4px;
        }

        .pagos-received-by {
            min-height: 58px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4px 8px;
        }

        .pagos-received-by-line {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            font-size: 12px;
            color: #64748b;
        }

        .pagos-received-by-line strong {
            color: #334155;
            font-size: 13px;
            font-weight: 800;
        }

        

        .pagos-received-date {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            color: #64748b;
        }


        /* =========================================================
        DETALLE
        ========================================================= */

        .pagos-detail-field {
            width: 100%;
        }


        /* =========================================================
        RESUMEN DE LA TRANSACCIÓN
        ========================================================= */

        .pagos-transaction-summary {
            margin-top: auto;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            background: #f8fafc;
        }

        .pagos-transaction-summary-row {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 18px;
            font-size: 12px;
            color: #64748b;
        }

        .pagos-transaction-summary-row strong {
            color: #111827;
            font-size: 15px;
            font-weight: 800;
        }

        .pagos-transaction-total {
            display: flex;
            margin-top: 8px;
            margin-right: 0;
        }

        .pagos-transaction-total strong {
            color: #15803d;
        }


        /* =========================================================
        BOTÓN ADICIONAR A LA COLA
        ========================================================= */

        .pagos-btn-add-queue {
            width: 100%;
            min-height: 42px;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 7px;
            background: #c81020;
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition:
                background-color .15s ease,
                transform .15s ease;
        }

        .pagos-btn-add-queue:hover {
            background: #a90d19;
            transform: translateY(-1px);
        }

        .pagos-btn-add-queue svg {
            width: 16px !important;
            height: 16px !important;
            flex: 0 0 16px;
        }


        /* =========================================================
        RESPONSIVE
        ========================================================= */

        @media (max-width: 1200px) {
            .pagos-register-layout {
                grid-template-columns: 1fr;
            }

            .pagos-payment-values-grid {
                width: 100%;
            }
        }

        @media (max-width: 720px) {
            .pagos-payment-values-grid,
            .pagos-payment-data-grid,
            .pagos-receipt-person-grid {
                grid-template-columns: 1fr;
            }
        }

        /* =========================================================
        COLA DE PAGOS
        ========================================================= */

        .pagos-clear-button {
            height: 29px;
            padding: 0 9px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid #fee2e2;
            border-radius: 6px;
            background: #ffffff;
            color: #dc2626;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .pagos-clear-button svg {
            width: 13px;
            height: 13px;
        }

        .pagos-queue {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pagos-queue-table-wrap {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            min-height: 218px;
            overflow-x: auto;
            overflow-y: auto;
        }

        .pagos-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            color: #334155;
        }

        .pagos-table th {
            padding: 8px 6px;
            background: #f8fafc;
            border-bottom: 1px solid #e5eaf0;
            text-align: left;
            white-space: nowrap;
            font-size: 11px;
            font-weight: 800;
            color: #475569;
        }

        .pagos-table td {
            padding: 9px 6px;
            border-bottom: 1px solid #edf1f5;
            vertical-align: middle;
            white-space: nowrap;
        }

        .pagos-table tr:last-child td {
            border-bottom: 0;
        }

        .pagos-table-number {
            font-weight: 800;
            color: #1e293b;
        }

        .pagos-table-actions {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .pagos-queue-summary {
            margin-top: auto;
            border-top: 1px solid #e4e9ef;
            background: #fbfcfd;
        }

        .pagos-queue-summary-row {
            padding: 9px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 10px;
            color: #334155;
        }

        .pagos-queue-summary-row strong {
            font-size: 13px;
            color: #1e293b;
        }

        .pagos-queue-total {
            padding: 14px 12px;
            border-top: 1px solid #dfe5ec;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 12px;
            font-weight: 800;
            color: #1e293b;
        }

        .pagos-queue-total strong {
            color: #078347;
            font-size: 22px;
        }

        .pagos-confirm-button {
            width: calc(100% - 24px);
            min-height: 42px;
            margin: 0 12px 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 7px;
            background: #c81020;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
        }

        .pagos-confirm-button:hover {
            background: #a90d19;
        }

        .pagos-confirm-button svg {
            width: 16px;
            height: 16px;
        }


        .pagos-queue-heading {
            min-width: 0;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .pagos-receipt-reference {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding-left: 9px;
            border-left: 1px solid #dbe2ea;
            font-size: 11px;
            color: #64748b;
        }

        .pagos-receipt-reference strong {
            font-size: 12px;
            font-weight: 800;
            color: #991b1b;
        }

        /* =========================================================
        COLA DE PAGOS - AJUSTE DE TIPOGRAFÍA
        ========================================================= */

        /* Texto general de la tabla: +1 punto */
        .pagos-queue .pagos-table {
            font-size: 13px;
        }

        /* Encabezados de columnas: +1 punto */
        .pagos-queue .pagos-table th {
            font-size: 12px;
        }

        /* Título de la tarjeta: +1 punto */
        .pagos-queue .pagos-panel-title {
            font-size: 16px;
        }

        /* Texto secundario "(pendientes por confirmar)": +1 punto */
        .pagos-queue .pagos-panel-title span {
            font-size: 14px;
        }

        /* Número de recibo: +2 puntos */
        .pagos-queue .pagos-receipt-reference {
            font-size: 13px;
        }

        .pagos-queue .pagos-receipt-reference strong {
            font-size: 14px;
        }

        /* Subtotal y descuentos: +2 puntos */
        .pagos-queue .pagos-queue-summary-row {
            font-size: 12px;
        }

        .pagos-queue .pagos-queue-summary-row strong {
            font-size: 15px;
        }

        /* Total a recibir: +2 puntos */
        .pagos-queue .pagos-queue-total {
            font-size: 14px;
        }

        .pagos-queue .pagos-queue-total strong {
            font-size: 24px;
        }

        /* Botón confirmar: +1 punto */
        .pagos-queue .pagos-confirm-button {
            font-size: 13px;
        }


        /* =========================================================
        OBLIGACIONES Y COSTOS CAUSADOS
        ========================================================= */

        .pagos-obligations {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .pagos-tabs {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 0 12px;
            border-bottom: 1px solid #e7ebf0;
        }

        .pagos-tab {
            position: relative;
            padding: 11px 0 9px;
            border: 0;
            background: transparent;
            color: #475569;
            font-size: 11px;
            cursor: pointer;
        }

        .pagos-tab-active {
            color: #dc2626;
            font-weight: 800;
        }

        .pagos-tab-active::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            height: 2px;
            border-radius: 999px;
            background: #dc2626;
        }

        .pagos-filter-button {
            width: 32px;
            height: 32px;
            margin-left: auto;
            display: grid;
            place-items: center;
            border: 1px solid #fecaca;
            border-radius: 6px;
            background: #ffffff;
            color: #dc2626;
            cursor: pointer;
        }

        .pagos-filter-button svg {
            width: 15px;
            height: 15px;
        }

        .pagos-obligations-table-wrap {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            height: 340px;
            overflow-x: auto;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .pagos-obligations-table {
            width: 100%;
            min-width: 620px;
        }


        .pagos-obligations-total td {
            font-weight: 800;
            background: #fbfcfd;
            color: #1e293b;
        }

        .pagos-obligations-note {
            padding: 7px 10px;
            border-top: 1px solid #edf1f5;
            font-size: 9px;
            color: #94a3b8;
        }

        /* =========================================================
        OBLIGACIONES - AJUSTE DE TIPOGRAFÍA
        ========================================================= */

        .pagos-obligations .pagos-panel-title {
            font-size: 16px;
        }

        .pagos-obligations .pagos-tab {
            font-size: 12px;
        }

        .pagos-obligations .pagos-table {
            font-size: 13px;
        }

        .pagos-obligations .pagos-table th {
            font-size: 12px;
        }

        .pagos-obligations .pagos-obligations-total td {
            font-size: 14px;
        }

        .pagos-obligations .pagos-obligations-note {
            font-size: 10px;
        }


        /* =========================================================
        CONTENCIÓN DE TABLAS INTERNAS
        ========================================================= */

        .pagos-queue,
        .pagos-obligations,
        .pagos-info-card {
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
        }

        .pagos-queue-table-wrap,
        .pagos-obligations-table-wrap {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow-x: auto;
            overflow-y: auto;
        }

        /* Evita que la tabla expanda la tarjeta */
        .pagos-queue-table-wrap .pagos-table,
        .pagos-obligations-table-wrap .pagos-table {
            width: max-content;
            min-width: 100%;
        }

        /* El scroll queda contenido dentro de la tarjeta */
        .pagos-queue-table-wrap,
        .pagos-obligations-table-wrap {
            scrollbar-width: thin;
        }


        /* =========================================================
        HISTORIAL DE PAGOS
        ========================================================= */

        .pagos-history-filters {
            display: grid;
            grid-template-columns:
                minmax(120px, 0.8fr)
                minmax(170px, 1.2fr)
                minmax(135px, 1fr)
                minmax(135px, 1fr)
                minmax(135px, 1fr);
            gap: 10px;
            padding: 10px 12px;
        }

        .pagos-history-filters .pagos-field label {
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
        }

        .pagos-history-filters .pagos-input,
        .pagos-history-filters .pagos-select {
            height: 32px;
            font-size: 11px;
        }

        .pagos-history-filter-button {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: 1px solid #fecaca;
            border-radius: 6px;
            background: #ffffff;
            color: #dc2626;
            cursor: pointer;
        }

        .pagos-history-filter-button svg {
            width: 15px;
            height: 15px;
        }

        .pagos-history-scroll {
            width: 100%;
            max-width: 100%;
            min-width: 0;

            max-height: 225px;

            overflow-x: auto;
            overflow-y: auto;

            scrollbar-width: thin;
        }

        .pagos-history-table {
            width: max-content;
            min-width: 980px;
            font-size: 13px;
        }

        .pagos-history-table th {
            font-size: 12px;
        }

        .pagos-history-receipt {
            color: #991b1b;
            font-weight: 800;
        }

        .pagos-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 23px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .pagos-status-confirmed {
            color: #18794e;
            background: #dff7e9;
            border: 1px solid #b8ebce;
        }

        .pagos-status-cancelled {
            color: #b91c1c;
            background: #feecec;
            border: 1px solid #fecaca;
        }

        .pagos-history-row-cancelled td {
            color: #94a3b8;
            background: #fffafa;
        }

        .pagos-icon-button-view {
            color: #475569;
        }

        .pagos-icon-button-print {
            color: #2563eb;
        }

        .pagos-history-footer {
            padding: 8px 12px;
            border-top: 1px solid #e5eaf0;
            background: #fbfcfd;
            font-size: 11px;
            color: #64748b;
        }

        .pagos-info-grid .pagos-history-table td {
            font-size: 13px;
        }

        @media (max-width: 1150px) {
            .pagos-history-filters {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .pagos-history-filters {
                grid-template-columns: 1fr;
            }
        }

        .pagos-history-select {
            appearance: auto !important;
            -webkit-appearance: auto !important;
            -moz-appearance: auto !important;
            background-image: none !important;
            padding-right: 12px;
        }


        /* =========================================================
        RESUMEN TARJETA PAGOS
        ========================================================= */
        .pagos-history-summary{
            margin-top:auto;
            border-top:1px solid #e5eaf0;
            background:#fbfcfd;
        }

        .pagos-history-summary-row{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:6px 14px;
            font-size:12px;          /* no cambia el tamaño del título */
            color:#475569;
            line-height:1.2;
        }

        .pagos-history-summary-row strong{
            font-size:13px;
            font-weight:700;
            color:#1e293b;
        }

        .pagos-history-summary-total{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:8px 14px;
            border-top:1px solid #dbe3ea;
            font-size:12px;          /* mantiene el texto */
            font-weight:700;
            line-height:1.2;
        }

        .pagos-history-summary-total strong{
            font-size:15px;
            font-weight:800;
            color:#078347;
        }


        /* =========================================================
        ACUERDOS DE PAGO
        ========================================================= */

        .pagos-agreement-add-button {
            min-height: 32px;
            padding: 0 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .pagos-agreement-add-button:hover {
            background: #dbeafe;
        }

        .pagos-agreement-add-button svg {
            width: 14px;
            height: 14px;
        }

        .pagos-agreements-scroll {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            max-height: 225px;
            overflow-x: auto;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .pagos-agreements-table {
            width: 100%;
            min-width: 820px;
            font-size: 13px;
        }

        .pagos-agreements-table th {
            font-size: 12px;
        }

        .pagos-agreements-table th:nth-child(1),
        .pagos-agreements-table td:nth-child(1) {
            width: 120px;
        }

        .pagos-agreements-table th:nth-child(2),
        .pagos-agreements-table td:nth-child(2) {
            width: 150px;
        }

        .pagos-agreements-table th:nth-child(4),
        .pagos-agreements-table td:nth-child(4) {
            width: 130px;
        }

        .pagos-agreements-table th:nth-child(5),
        .pagos-agreements-table td:nth-child(5) {
            width: 80px;
        }

        .pagos-agreement-summary {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #334155;
        }

        .pagos-evidence-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 7px;
            border: 1px solid #dbeafe;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .pagos-evidence-badge svg {
            width: 12px;
            height: 12px;
        }

        .pagos-evidence-empty {
            color: #94a3b8;
            font-size: 11px;
        }


        /* =========================================================
        RESPONSIVE DE LA ZONA OPERATIVA
        ========================================================= */

        @media (max-width: 1200px) {
            .pagos-work-grid {
                grid-template-columns: minmax(500px, 1.4fr) minmax(380px, 1fr);
            }

            .pagos-info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1050px) {
            .pagos-work-grid {
                grid-template-columns: 1fr;
            }

            .pagos-info-grid {
                grid-template-columns: 1fr;
            }

            .pagos-obligations-table-wrap,
            .pagos-history-scroll {
                height: 320px;
            }
        }

        @media (max-width: 560px) {
            .pagos-field-grid-3,
            .pagos-field-grid-2 {
                grid-template-columns: 1fr;
            }

            .pagos-payment-methods-header {
                display: none;
            }

            .pagos-payment-method-row {
                grid-template-columns: 1fr;
            }

            .pagos-register-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .pagos-btn-primary {
                width: 100%;
            }
        }

        @media (max-width: 1250px) {
            .pagos-student-card {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 820px) {
            .pagos-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .pagos-header-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .pagos-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .pagos-summary-grid {
                grid-template-columns: 1fr;
            }

            .pagos-student-info {
                align-items: flex-start;
            }

            .pagos-student-name-row {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        /* =========================================================
           RESPONSIVE FINAL DE LA PANTALLA DE PAGOS
           Este bloque sobrescribe únicamente distribución y tamaños
           adaptables. No modifica colores ni diseño visual.
        ========================================================= */

        .pagos-page,
        .pagos-page *,
        .pagos-page *::before,
        .pagos-page *::after {
            box-sizing: border-box;
        }

        .pagos-page,
        .pagos-card,
        .pagos-panel,
        .pagos-student-card,
        .pagos-work-grid,
        .pagos-info-grid,
        .pagos-register-layout,
        .pagos-register-column,
        .pagos-summary-grid,
        .pagos-history-filters {
            min-width: 0;
            max-width: 100%;
        }

        .pagos-header,
        .pagos-student-info,
        .pagos-student-name-row,
        .pagos-header-actions,
        .pagos-panel-header,
        .pagos-queue-heading {
            min-width: 0;
        }

        .pagos-header-actions,
        .pagos-panel-header {
            flex-wrap: wrap;
        }

        .pagos-work-grid,
        .pagos-info-grid {
            width: 100%;
        }

        .pagos-queue-table-wrap,
        .pagos-obligations-table-wrap,
        .pagos-history-scroll,
        .pagos-agreements-scroll {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
        }

        .pagos-queue-table-wrap .pagos-table,
        .pagos-obligations-table-wrap .pagos-table,
        .pagos-history-table,
        .pagos-agreements-table {
            width: max-content;
            min-width: 100%;
        }

        @media (max-width: 1500px) {
            .pagos-work-grid {
                grid-template-columns:
                    minmax(0, 1.55fr)
                    minmax(360px, .95fr);
            }

            .pagos-info-grid {
                grid-template-columns:
                    minmax(0, 1.08fr)
                    minmax(0, .92fr);
            }

            .pagos-student-card {
                grid-template-columns:
                    minmax(330px, .82fr)
                    minmax(0, 1.18fr);
            }

            .pagos-register-layout {
                grid-template-columns:
                    minmax(0, 1.05fr)
                    minmax(0, .95fr);
            }

            .pagos-payment-values-grid {
                width: 82%;
            }

            .pagos-payment-data-grid {
                grid-template-columns:
                    minmax(115px, 1fr)
                    minmax(115px, 1fr)
                    minmax(105px, .8fr);
                gap: 10px;
            }
        }

        @media (max-width: 1280px) {
            .pagos-student-card,
            .pagos-work-grid,
            .pagos-info-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .pagos-work-grid > .pagos-panel,
            .pagos-work-grid > .pagos-panel > .pagos-panel-body {
                height: auto;
            }

            .pagos-register-column {
                height: auto;
            }

            .pagos-register-layout {
                grid-template-columns:
                    minmax(0, 1fr)
                    minmax(0, 1fr);
            }

            .pagos-register-column-payment {
                min-height: 380px;
            }

            .pagos-register-column-info {
                min-height: 380px;
            }

            .pagos-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .pagos-history-filters {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 1050px) {
            .pagos-register-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .pagos-register-column-payment,
            .pagos-register-column-info {
                min-height: auto;
            }

            .pagos-register-column-payment {
                gap: 18px;
                justify-content: flex-start;
            }

            .pagos-payment-values-grid {
                width: 100%;
            }

            .pagos-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pagos-history-filters {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pagos-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            .pagos-header-actions {
                width: 100%;
            }

            .pagos-header-actions .pagos-btn {
                flex: 1 1 220px;
            }
        }

        @media (max-width: 760px) {
            .pagos-page {
                gap: 10px;
            }

            .pagos-header h1 {
                font-size: 24px;
            }

            .pagos-student-card {
                padding: 10px;
            }

            .pagos-student-info {
                align-items: flex-start;
            }

            .pagos-student-name-row {
                align-items: flex-start;
                flex-direction: column;
                gap: 5px;
            }

            .pagos-student-name-row h2 {
                white-space: normal;
                overflow: visible;
            }

            .pagos-summary-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .pagos-payment-values-grid,
            .pagos-payment-data-grid,
            .pagos-receipt-person-grid {
                grid-template-columns: minmax(0, 1fr);
                width: 100%;
            }

            .pagos-register-column {
                padding: 10px;
            }

            .pagos-history-filters {
                grid-template-columns: minmax(0, 1fr);
            }

            .pagos-panel-header {
                align-items: flex-start;
            }

            .pagos-queue-heading {
                align-items: flex-start;
                flex-direction: column;
                gap: 5px;
            }

            .pagos-receipt-reference {
                padding-left: 0;
                border-left: 0;
            }

            .pagos-history-scroll,
            .pagos-agreements-scroll {
                max-height: 225px;
            }
        }

        @media (max-width: 560px) {
            .pagos-header-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .pagos-header-actions .pagos-btn {
                width: 100%;
                flex-basis: auto;
            }

            .pagos-panel-header {
                padding: 10px;
            }

            .pagos-panel-title,
            .pagos-queue .pagos-panel-title,
            .pagos-obligations .pagos-panel-title {
                font-size: 15px;
            }

            .pagos-clear-button,
            .pagos-agreement-add-button {
                flex: 0 0 auto;
            }

            .pagos-tabs {
                gap: 14px;
                overflow-x: auto;
            }

            .pagos-transaction-summary-row {
                display: flex;
                margin-right: 0;
                margin-bottom: 5px;
            }

            .pagos-transaction-total {
                margin-top: 5px;
            }

            .pagos-btn-add-queue,
            .pagos-confirm-button {
                font-size: 12px;
            }

            .pagos-confirm-button {
                width: calc(100% - 20px);
                margin: 0 10px 10px;
            }
        }

        .pagos-btn-add-queue:disabled {
            background: #cbd5e1;
            color: #64748b;
            cursor: not-allowed;
            transform: none;
            opacity: .8;
        }

        .pagos-btn-add-queue:disabled:hover {
            background: #cbd5e1;
            transform: none;
        }

        .pagos-history-receipt-group td {
            padding: 9px 12px;
            background: #f8fafc;
            border-top: 2px solid #dbe2ea;
            border-bottom: 1px solid #dbe2ea;
        }

        .pagos-history-receipt-group:first-child td {
            border-top: none;
        }

        .pagos-history-receipt-group-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .pagos-history-receipt-group-content > div {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px 14px;
        }

        .pagos-history-receipt-group-content strong {
            color: #991b1b;
            font-size: 12px;
        }

        .pagos-history-receipt-group-content span:not(.pagos-status) {
            color: #64748b;
            font-size: 10px;
        }

        .pagos-history-concept {
            display: flex;
            align-items: flex-start;
            flex-direction: column;
            gap: 4px;
        }

        .pagos-concept-badge {
            display: inline-flex;
            align-items: center;
            width: max-content;
            padding: 1px 5px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 700;
            line-height: 1.15;
        }

        .pagos-concept-badge-required {
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        

        .pagos-history-reference {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 9px;
        }


        .pagos-history-receipt-button {
            display: block;
            width: 100%;
            padding: 0;
            border: 0;
            background: transparent;
            text-align: left;
            cursor: pointer;
        }

        .pagos-history-receipt-button:hover {
            background: #f1f5f9;
        }

        .pagos-history-receipt-button:focus-visible {
            outline: 2px solid #93c5fd;
            outline-offset: -2px;
        }

        .pagos-history-receipt-group-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pagos-history-receipt-group-right svg {
            width: 15px;
            height: 15px;
            color: #64748b;
        }

        .pagos-slideover-backdrop {
            position: fixed;
            inset: 0;
            z-index: 90;
            background: rgba(15, 23, 42, 0.38);
        }

        .pagos-slideover {
            position: fixed;
            top: 0;
            right: 0;
            z-index: 100;
            width: min(520px, 92vw);
            height: 100dvh;
            max-height: 100dvh;
            background: #ffffff;
            border-left: 1px solid #e2e8f0;
            box-shadow: -12px 0 30px rgba(15, 23, 42, 0.16);

            display: flex;
            flex-direction: column;

            overflow: hidden;
        }

        .pagos-slideover-header {
            flex: 0 0 auto;

            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;

            padding: 16px 18px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .pagos-slideover-eyebrow {
            display: block;
            color: #64748b;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .pagos-slideover-header h2 {
            margin: 0;
            font-size: 21px;
            font-weight: 800;
            color: #0f172a;
        }

        .pagos-slideover-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            cursor: pointer;
        }

        .pagos-slideover-close svg {
            width: 18px;
            height: 18px;
        }

        .pagos-slideover-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 16px 18px;
        }

        .pagos-slideover-loading {
            padding: 30px 10px;
            text-align: center;
            color: #94a3b8;
        }


        /* =========================================================
        DETALLE DEL RECIBO
        ========================================================= */

        .pagos-receipt-detail {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .pagos-receipt-detail-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
        }

        .pagos-receipt-detail-summary > div {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .pagos-receipt-detail-summary span:not(.pagos-status),
        .pagos-receipt-detail-grid span,
        .pagos-receipt-detail-line-grid span,
        .pagos-receipt-detail-totals span {
            color: #64748b;
            font-size: 11px;
            line-height: 1.25;
        }

        .pagos-receipt-detail-summary strong {
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .pagos-receipt-detail-section {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            overflow: hidden;
        }

        .pagos-receipt-detail-section > h3 {
            margin: 0;
            padding: 11px 14px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
        }

        .pagos-receipt-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 16px;
            padding: 14px;
        }

        .pagos-receipt-detail-grid > div {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .pagos-receipt-detail-grid strong {
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .pagos-receipt-detail-lines {
            display: flex;
            flex-direction: column;
        }

        .pagos-receipt-detail-line {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        .pagos-receipt-detail-line:last-child {
            border-bottom: 0;
        }

        .pagos-receipt-detail-line-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .pagos-receipt-detail-line-heading > div {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .pagos-receipt-detail-line-heading strong {
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .pagos-receipt-detail-line-heading > div > span {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
        }

        .pagos-receipt-detail-line-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 14px;
        }

        .pagos-receipt-detail-line-grid > div {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .pagos-receipt-detail-line-grid strong {
            color: #0f172a;
            font-size: 13px;
            font-weight: 750;
            overflow-wrap: anywhere;
        }

        .pagos-receipt-detail-line-grid small {
            margin-top: 2px;
            color: #64748b;
            font-size: 10px;
            overflow-wrap: anywhere;
        }

        .pagos-receipt-detail-note {
            margin-top: 11px;
            padding: 9px 10px;
            border-radius: 7px;
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .pagos-receipt-detail-totals {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            overflow: hidden;
        }

        .pagos-receipt-detail-totals > div {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        .pagos-receipt-detail-totals > div:last-child {
            border-bottom: 0;
        }

        .pagos-receipt-detail-totals strong {
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .pagos-receipt-detail-total {
            background: #f8fafc;
        }

        .pagos-receipt-detail-total span {
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
        }

        .pagos-receipt-detail-total strong {
            color: #008c45;
            font-size: 19px;
            font-weight: 900;
        }

        .pagos-text-warning {
            color: #b45309 !important;
        }

        .pagos-text-green {
            color: #008c45 !important;
        }

        /* =========================================================
        PIE DEL SLIDEOVER
        ========================================================= */

        

        .pagos-slideover-footer-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 38px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
        }

        .pagos-slideover-footer-button svg {
            width: 16px;
            height: 16px;
        }

        .pagos-slideover-footer-button-secondary {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .pagos-slideover-footer-button-secondary:hover {
            background: #f8fafc;
        }

        .pagos-slideover-footer-button-primary {
            border: 1px solid #b91c1c;
            background: #b91c1c;
            color: #ffffff;
        }

        .pagos-slideover-footer-button-primary:hover {
            background: #991b1b;
        }


        .pagos-slideover-wrapper {
            position: fixed;
            inset: 0;
            z-index: 90;
        }

        .pagos-slideover-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.38);
        }

        .pagos-slideover {
            position: absolute;
            top: 0;
            right: 0;

            width: min(520px, 92vw);
            height: 100vh;
            height: 100dvh;
            max-height: 100dvh;

            display: grid;
            grid-template-rows:
                auto
                minmax(0, 1fr)
                auto;

            overflow: hidden;

            background: #ffffff;
            border-left: 1px solid #e2e8f0;
            box-shadow: -12px 0 30px rgba(15, 23, 42, 0.16);
        }

        .pagos-slideover-header {
            grid-row: 1;

            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;

            padding: 16px 18px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .pagos-slideover-body {
            grid-row: 2;

            min-width: 0;
            min-height: 0;

            overflow-x: hidden;
            overflow-y: auto;

            padding: 16px 18px;

            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            -webkit-overflow-scrolling: touch;
        }

        .pagos-slideover-footer {
            grid-row: 3;

            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;

            padding: 12px 18px;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
        }

        /* =========================================================
        RESPONSIVE
        ========================================================= */

        @media (max-width: 700px) {
            .pagos-receipt-detail-summary {
                grid-template-columns: 1fr;
            }

            .pagos-receipt-detail-grid,
            .pagos-receipt-detail-line-grid {
                grid-template-columns: 1fr;
            }

            .pagos-slideover-footer {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .pagos-slideover-footer-button {
                width: 100%;
            }

            .pagos-print-history-item {
                grid-template-columns: 1fr;
            }

            .pagos-modal-footer {
                flex-direction: column-reverse;
                align-items: stretch;
            }
        }


        /* =========================================================
        MODAL DE REIMPRESIÓN
        ========================================================= */

        .pagos-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 120;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.52);
        }

        .pagos-modal-card {
            width: min(520px, 94vw);
            max-height: 90vh;
            overflow: hidden;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.26);
        }

        .pagos-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .pagos-modal-header span {
            display: block;
            margin-bottom: 3px;
            color: #64748b;
            font-size: 11px;
        }

        .pagos-modal-header h3 {
            margin: 0;
            color: #0f172a;
            font-size: 19px;
            font-weight: 800;
        }

        .pagos-modal-body {
            min-height: 0;
            overflow-y: auto;
            padding: 20px;
        }

        .pagos-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .pagos-textarea {
            width: 100%;
            min-height: 110px;
            resize: vertical;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.45;
        }

        .pagos-textarea:focus {
            outline: none;
            border-color: #94a3b8;
            box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.18);
        }

        .pagos-reprint-preview {
            margin-top: 14px;
            padding: 11px 12px;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            background: #eff6ff;
            color: #475569;
            font-size: 11px;
        }

        .pagos-reprint-preview strong {
            display: inline-block;
            margin-left: 5px;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 800;
        }

        /* =========================================================
        HISTORIAL DE IMPRESIÓN
        ========================================================= */

        .pagos-print-history {
            display: flex;
            flex-direction: column;
        }

        .pagos-print-history-item {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px 14px;
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        .pagos-print-history-item > div {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .pagos-print-history-item span,
        .pagos-print-history-total span {
            color: #64748b;
            font-size: 11px;
        }

        .pagos-print-history-item strong,
        .pagos-print-history-total strong {
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .pagos-print-history-reason {
            grid-column: 1 / -1;
        }

        .pagos-print-history-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            background: #f8fafc;
        }

        .pagos-print-history-total strong {
            font-size: 14px;
            font-weight: 900;
        }

        .pagos-print-history-empty {
            padding: 18px 14px;
            color: #64748b;
            font-size: 11px;
            text-align: center;
        }



    </style>


    <div class="pagos-page">

        {{-- =========================================================
            2. ENCABEZADO DE LA PANTALLA
        ========================================================== --}}
        <div class="pagos-header">
            <div>
                <h1>Pagos</h1>
                <p>Gestión de pagos, acuerdos y recibos del estudiante.</p>
            </div>

            <div class="pagos-header-actions">
                <button type="button" class="pagos-btn pagos-btn-outline-blue">
                    <x-heroicon-o-document-chart-bar class="pagos-btn-icon" />
                    Extracto
                </button>

                <button type="button" class="pagos-btn pagos-btn-outline-dark">
                    <x-heroicon-o-printer class="pagos-btn-icon" />
                    Imprimir / Reimprimir
                    <x-heroicon-o-chevron-down class="pagos-btn-chevron" />
                </button>
            </div>
        </div>


        {{-- =========================================================
            3. BÚSQUEDA E INFORMACIÓN DEL ESTUDIANTE
        ========================================================== --}}
        <section class="pagos-card pagos-student-card">

            <div class="pagos-student-left">

                <div class="pagos-search">
                    <input
                        type="text"
                        wire:model.live.debounce.350ms="buscarEstudiante"
                        placeholder="Buscar por código, identificación o nombre del estudiante..."
                        autocomplete="off"
                    >

                    @if($buscarEstudiante !== '')
                        <button
                            type="button"
                            class="pagos-search-clear"
                            wire:click="limpiarEstudiante"
                            title="Limpiar búsqueda"
                        >
                            <x-heroicon-o-x-mark />
                        </button>
                    @endif

                    <x-heroicon-o-magnifying-glass class="pagos-search-icon" />

                    @if(count($resultadosBusqueda) > 0)
                        <div class="pagos-search-results">
                            @foreach($resultadosBusqueda as $resultado)
                                <button
                                    type="button"
                                    class="pagos-search-result"
                                    wire:click="seleccionarEstudiante({{ $resultado['id'] }})"
                                    wire:key="resultado-estudiante-{{ $resultado['id'] }}"
                                >
                                    <span class="pagos-search-result-main">
                                        <span class="pagos-search-result-name">
                                            {{ $resultado['nombre'] }}
                                        </span>

                                        <span class="pagos-search-result-meta">
                                            Código: {{ $resultado['codigo'] }}
                                            · Documento: {{ $resultado['documento'] }}
                                        </span>
                                    </span>

                                    <span class="pagos-search-result-course">
                                        {{ $resultado['grado'] }} · {{ $resultado['curso'] }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @elseif(
                        mb_strlen(trim($buscarEstudiante)) >= 2
                        && empty($estudianteSeleccionado)
                    )
                        <div class="pagos-search-results">
                            <div class="pagos-search-empty">
                                No se encontraron estudiantes en la sede y periodo lectivo activos.
                            </div>
                        </div>
                    @endif
                </div>

                @if(! empty($estudianteSeleccionado))
                    <div class="pagos-student-info">

                        <div class="pagos-avatar">
                            {{ $estudianteSeleccionado['iniciales'] }}
                        </div>

                        <div class="pagos-student-data">
                            <div class="pagos-student-name-row">
                                <h2>
                                    {{ $estudianteSeleccionado['nombre'] }}
                                </h2>

                                <span class="pagos-badge pagos-badge-success">
                                    {{ ucfirst($estudianteSeleccionado['estado']) }}
                                </span>
                            </div>

                            <div class="pagos-student-meta">
                                <span>
                                    <strong>Código:</strong>
                                    {{ $estudianteSeleccionado['codigo'] }}
                                </span>

                                <span class="pagos-divider">|</span>

                                <span>
                                    <strong>Identificación:</strong>
                                    {{ $estudianteSeleccionado['documento'] }}
                                </span>
                            </div>

                            <div class="pagos-student-meta">
                                <span>
                                    <strong>Grado:</strong>
                                    {{ $estudianteSeleccionado['grado'] }}
                                </span>

                                <span class="pagos-divider">|</span>

                                <span>
                                    <strong>Curso:</strong>
                                    {{ $estudianteSeleccionado['curso'] }}
                                </span>

                                <span class="pagos-divider">|</span>

                                <span>
                                    <strong>Acudiente:</strong>

                                    @if($estudianteSeleccionado['acudiente'])
                                        {{ $estudianteSeleccionado['acudiente'] }}

                                        @if($estudianteSeleccionado['parentesco'])
                                            ({{ ucfirst($estudianteSeleccionado['parentesco']) }})
                                        @endif
                                    @else
                                        Sin acudiente registrado
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="pagos-student-placeholder">
                        Busque y seleccione un estudiante para consultar su información financiera.
                    </div>
                @endif

            </div>


            {{-- =========================================================
                4. RESUMEN FINANCIERO
            ========================================================== --}}
            <div class="pagos-summary-grid">

                <article class="pagos-summary-item">
                    <div class="pagos-summary-icon pagos-summary-icon-red">
                        <x-heroicon-o-document-currency-dollar />
                    </div>

                    <div>
                        <span>Deuda obligatoria</span>

                        <strong class="pagos-text-red">
                            $ {{ number_format(
                                $resumenFinanciero['deuda_obligatoria'] ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>
                </article>

                <article class="pagos-summary-item">
                    <div class="pagos-summary-icon pagos-summary-icon-orange">
                        <x-heroicon-o-exclamation-triangle />
                    </div>

                    <div>
                        <span>Penalizaciones</span>

                        <strong class="pagos-text-orange">
                            $ {{ number_format(
                                $resumenFinanciero['penalizaciones'] ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>
                </article>

                <article class="pagos-summary-item">
                    <div class="pagos-summary-icon pagos-summary-icon-blue">
                        <x-heroicon-o-receipt-percent />
                    </div>

                    <div>
                        <span>Otros conceptos</span>

                        <strong class="pagos-text-blue">
                            $ {{ number_format(
                                $resumenFinanciero['otros_conceptos'] ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>
                </article>

                <article class="pagos-summary-item pagos-summary-soft-red">
                    <div class="pagos-summary-icon pagos-summary-icon-red">
                        <x-heroicon-o-banknotes />
                    </div>

                    <div>
                        <span>Total pendiente</span>

                        <strong class="pagos-text-red">
                            $ {{ number_format(
                                $resumenFinanciero['total_pendiente'] ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>
                </article>

                <article class="pagos-summary-item pagos-summary-soft-green">
                    <div class="pagos-summary-icon pagos-summary-icon-green">
                        <x-heroicon-o-scale />
                    </div>

                    <div>
                        <span>Saldo a favor</span>

                        <strong class="pagos-text-green">
                            $ {{ number_format(
                                $resumenFinanciero['saldo_favor'] ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>
                </article>

                <article class="pagos-summary-item pagos-summary-net">
                    <div>
                        <span>Total neto pendiente</span>

                        <strong>
                            $ {{ number_format(
                                $resumenFinanciero['total_neto'] ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>
                </article>

            </div>
        </section>


        {{-- =========================================================
            5. ZONA PRINCIPAL DE PAGO
            Registrar pago + cola de pagos
        ========================================================== --}}
        <div class="pagos-work-grid">

            {{-- =====================================================
                5.1. REGISTRAR PAGO
            ====================================================== --}}
            <section class="pagos-card pagos-panel">

                <div class="pagos-panel-header">
                    <h2 class="pagos-panel-title">
                        1. Registrar pago
                    </h2>
                </div>

                <div class="pagos-panel-body">

                    <div class="pagos-register-layout">

                        {{-- =====================================================
                            COLUMNA IZQUIERDA
                            Información económica de la transacción
                        ====================================================== --}}
                        <div class="pagos-register-column pagos-register-column-payment">

                            {{-- Concepto --}}
                            <div class="pagos-field">
                                <label>
                                    Concepto u obligación
                                    <span class="pagos-required">*</span>
                                </label>

                                <select
                                    class="pagos-select"
                                    wire:model.live="movimientoSeleccionadoId"
                                    @disabled(empty($estudianteSeleccionado))
                                >
                                    <option value="">Seleccione una obligación</option>

                                    @foreach($obligaciones as $obligacion)
                                        <option value="{{ $obligacion['id'] }}">
                                            {{ $obligacion['concepto'] }}
                                            {{ $obligacion['mes'] ? ' - ' . $obligacion['mes'] : '' }}
                                            — Saldo ${{ number_format($obligacion['saldo_pendiente'], 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Mes o periodo --}}
                            <div class="pagos-field">
                                <label>Mes asociado</label>

                                <input
                                    type="text"
                                    class="pagos-input"
                                    wire:model="mesPeriodoPago"
                                    readonly
                                >

                                <small style="
                                    display:block;
                                    margin-top:5px;
                                    font-size:10px;
                                    color:#94a3b8;
                                ">
                                    Solo aplica para obligaciones mensuales.
                                </small>
                            </div>

                            {{-- Valor y descuento --}}
                            <div class="pagos-payment-values-grid">

                                <div class="pagos-field">
                                    <label>
                                        Valor que paga
                                        <span class="pagos-required">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        class="pagos-input pagos-input-money"
                                        wire:model.blur="valorPago"
                                        placeholder="0"
                                        @disabled(! $movimientoSeleccionadoId)
                                    >
                                </div>

                                <div class="pagos-field">
                                    <label>Descuento</label>

                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        class="pagos-input pagos-input-money"
                                        wire:model.blur="descuentoPago"
                                        placeholder="0"
                                        @disabled(! $movimientoSeleccionadoId)
                                    >
                                </div>

                            </div>

                            {{-- Forma de pago, referencia y fecha --}}
                            <div class="pagos-payment-data-grid">

                                <div class="pagos-field">
                                    <label>
                                        Forma de pago
                                        <span class="pagos-required">*</span>
                                    </label>

                                    <select
                                        class="pagos-select"
                                        wire:model.live="formaPagoId"
                                        @disabled(empty($formasPago))
                                    >
                                        <option value="">Seleccione</option>

                                        @foreach($formasPago as $forma)
                                            <option value="{{ $forma['id'] }}">
                                                {{ $forma['nombre'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="pagos-field">
                                    <label>Referencia / Consignación</label>

                                    <input
                                        type="text"
                                        class="pagos-input"
                                        wire:model="referenciaPago"
                                        placeholder="-"
                                        @disabled(! ($this->formaPagoSeleccionada['requiere_referencia'] ?? false))
                                    >
                                </div>

                                <div class="pagos-field">
                                    <label>Fecha</label>

                                    <input
                                        type="date"
                                        class="pagos-input"
                                        wire:model="fechaConsignacion"
                                        @disabled(! ($this->formaPagoSeleccionada['requiere_fecha_consignacion'] ?? false))
                                    >
                                </div>

                            </div>

                        </div>


                        {{-- =====================================================
                            COLUMNA DERECHA
                            Información administrativa del recibo
                        ====================================================== --}}
                        <div class="pagos-register-column pagos-register-column-info">

                            <div class="pagos-receipt-person-grid">

                                {{-- Usuario de sesión --}}
                                <div class="pagos-received-by">

                                    <div class="pagos-received-by-line">
                                        <span>Recibido por:</span>
                                        <strong>{{ auth()->user()?->name ?? 'Usuario' }}</strong>
                                        
                                    </div>

                                    <span class="pagos-received-date">
                                        {{ now()->format('d/m/Y · h:i a') }}
                                    </span>

                                </div>

                                {{-- Persona que entrega el dinero --}}
                                <div class="pagos-field">
                                    <label>
                                        Recibí de
                                        <span class="pagos-required">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="pagos-input"
                                        wire:model.live.debounce.300ms="recibiDe"
                                        placeholder="Ej.: María Pérez (Madre)"
                                    >
                                </div>

                            </div>

                            {{-- Detalle --}}
                            <div class="pagos-field pagos-detail-field">
                                <label>Detalle (opcional)</label>

                                <input
                                    type="text"
                                    class="pagos-input"
                                    wire:model.live.debounce.300ms="detallePago"
                                    placeholder="Ej.: Abono, acuerdo verbal, anticipo, etc."
                                >
                            </div>

                            {{-- Resumen de esta transacción --}}
                            <div class="pagos-transaction-summary">

                                <div class="pagos-transaction-summary-row">
                                    <span>Valor a pagar</span>

                                    <strong>
                                        $ {{ number_format(
                                            $this->valorPagoNumerico,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>
                                </div>

                                <div class="pagos-transaction-summary-row">
                                    <span>Descuento</span>

                                    <strong>
                                        $ {{ number_format(
                                            $this->descuentoPagoNumerico,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>
                                </div>

                                <div class="pagos-transaction-summary-row pagos-transaction-total">
                                    <span>Total recibido</span>

                                    <strong>
                                        $ {{ number_format(
                                            $this->totalTransaccion,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>
                                </div>

                            </div>

                            <button
                                type="button"
                                class="pagos-btn-add-queue"
                                wire:click="adicionarPagoCola"
                                wire:loading.attr="disabled"
                                wire:target="adicionarPagoCola"
                                @disabled(! $this->puedeAdicionarPago)
                            >
                                <x-heroicon-o-shopping-cart />

                                <span wire:loading.remove wire:target="adicionarPagoCola">
                                    Adicionar este pago a la cola
                                </span>

                                <span wire:loading wire:target="adicionarPagoCola">
                                    Adicionando...
                                </span>
                            </button>

                        </div>

                    </div>

                </div>

            </section>




            {{-- =====================================================
                5.2. COLA DE PAGOS
            ====================================================== --}}
            <section class="pagos-card pagos-panel pagos-queue">

                <div class="pagos-panel-header">
                    <div class="pagos-queue-heading">
                        <h2 class="pagos-panel-title">
                            2. Cola de pagos
                        </h2>

                        <span class="pagos-receipt-reference">
                            <span>Recibo N.º</span>

                            <strong>
                                @if($ultimoNumeroRecibo)
                                    {{ $ultimoNumeroRecibo }}
                                @else
                                    Se asigna al confirmar
                                @endif
                            </strong>
                        </span>
                    </div>

                    <button
                        type="button"
                        class="pagos-clear-button"
                        wire:click="limpiarColaPagos"
                        @disabled(empty($colaPagos))
                    >
                        <x-heroicon-o-trash />
                        Limpiar cola
                    </button>
                </div>

                <div class="pagos-queue-table-wrap">

                    <table class="pagos-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Mes</th>
                                <th>Desc.</th>
                                <th>Valor paga</th>
                                <th>Forma de pago</th>
                                <th>Acc.</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($colaPagos as $fila)
                                <tr wire:key="pago-cola-{{ $fila['fila_id'] }}">
                                    <td>
                                        {{ $fila['concepto'] }}
                                    </td>

                                    <td>
                                        {{ $fila['mes'] }}
                                    </td>

                                    <td>
                                        $ {{ number_format(
                                            $fila['descuento'] ?? 0,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                    <td>
                                        $ {{ number_format(
                                            $fila['valor_recibido'] ?? 0,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                        @php
                                            $pendienteEnCola = $this->pendienteObligacionEnCola(
                                                (int) $fila['movimiento_id']
                                            );

                                            $esUltimaFila = $this->esUltimaFilaDeObligacion(
                                                (int) $fila['fila_id'],
                                                (int) $fila['movimiento_id']
                                            );
                                        @endphp

                                        @if($esUltimaFila && $pendienteEnCola > 0)
                                            <div style="
                                                margin-top:3px;
                                                font-size:10px;
                                                color:#b45309;
                                                font-weight:700;
                                            ">
                                                Pendiente:
                                                $ {{ number_format(
                                                    $pendienteEnCola,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </div>
                                        @endif

                                        @if(($fila['saldo_favor_generado'] ?? 0) > 0)
                                            <div style="
                                                margin-top:3px;
                                                font-size:10px;
                                                color:#15803d;
                                                font-weight:700;
                                            ">
                                                Saldo a favor:
                                                $ {{ number_format(
                                                    $fila['saldo_favor_generado'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $fila['forma_pago'] }}
                                    </td>

                                    <td>
                                        <div class="pagos-table-actions">
                                            <button
                                                type="button"
                                                class="pagos-icon-button pagos-icon-button-danger"
                                                wire:click="eliminarPagoCola({{ $fila['fila_id'] }})"
                                                title="Eliminar de la cola"
                                            >
                                                <x-heroicon-o-trash />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="6"
                                        style="
                                            height:150px;
                                            text-align:center;
                                            color:#94a3b8;
                                        "
                                    >
                                        No hay pagos pendientes por confirmar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>

                <div class="pagos-queue-summary">

                    <div class="pagos-queue-summary-row">
                        <span>Subtotal recibido</span>

                        <strong>
                            $ {{ number_format(
                                $this->subtotalCola,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>

                    <div class="pagos-queue-summary-row">
                        <span>Descuentos</span>

                        <strong>
                            $ {{ number_format(
                                $this->descuentosCola,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>

                    @if($this->saldoFavorGeneradoCola > 0)
                        <div class="pagos-queue-summary-row">
                            <span>Saldo a favor generado</span>

                            <strong class="pagos-text-green">
                                $ {{ number_format(
                                    $this->saldoFavorGeneradoCola,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </strong>
                        </div>
                    @endif

                    <div class="pagos-queue-total">
                        <span>Total a recibir</span>

                        <strong>
                            $ {{ number_format(
                                $this->subtotalCola,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>

                    <button
                        type="button"
                        class="pagos-confirm-button"
                        wire:click="confirmarPagos"
                        wire:loading.attr="disabled"
                        wire:target="confirmarPagos"
                        @disabled(! $this->puedeConfirmarCola)
                    >
                        <x-heroicon-o-check />

                        <span wire:loading.remove wire:target="confirmarPagos">
                            Confirmar y registrar pagos
                        </span>

                        <span wire:loading wire:target="confirmarPagos">
                            Registrando pagos...
                        </span>
                    </button>

                </div>

            </section>


            {{-- =========================================================
                6. ZONA DE CONSULTA
                Obligaciones + historial de pagos
            ========================================================== --}}
            <div class="pagos-info-grid">

            {{-- =====================================================
                5.3. OBLIGACIONES Y COSTOS CAUSADOS
            ====================================================== --}}
            <section class="pagos-card pagos-panel pagos-obligations pagos-info-card">

                <div class="pagos-panel-header">
                    <h2 class="pagos-panel-title">
                        3. Obligaciones y costos causados
                    </h2>

                    <button type="button" class="pagos-filter-button">
                        <x-heroicon-o-funnel />
                    </button>
                </div>

                <div class="pagos-tabs">

                    <button
                        type="button"
                        wire:click="cambiarTipoObligacion('obligatorio')"
                        class="pagos-tab {{ $tipoObligacionActiva === 'obligatorio' ? 'pagos-tab-active' : '' }}"
                    >
                        Obligatorios ({{ count($this->obligacionesObligatorias) }})
                    </button>

                    <button
                        type="button"
                        wire:click="cambiarTipoObligacion('no_obligatorio')"
                        class="pagos-tab {{ $tipoObligacionActiva === 'no_obligatorio' ? 'pagos-tab-active' : '' }}"
                    >
                        No obligatorios ({{ count($this->obligacionesNoObligatorias) }})
                    </button>

                </div>

                <div class="pagos-obligations-table-wrap">

                    <table class="pagos-table pagos-obligations-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Mes</th>
                                <th>Vencimiento</th>
                                <th>Valor a pagar a la fecha</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($this->obligacionesVisibles as $obligacion)
                                <tr wire:key="obligacion-{{ $obligacion['id'] }}">
                                    <td>
                                        {{ $obligacion['concepto'] }}
                                    </td>

                                    <td>
                                        {{ $obligacion['mes_mostrado'] }}
                                    </td>

                                    <td>
                                        {{ $obligacion['fecha_vencimiento_formateada'] }}
                                    </td>

                                    <td class="pagos-table-number">
                                        $ {{ number_format(
                                            $obligacion['saldo_pendiente'] ?? 0,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                        @if(($obligacion['valor_aplicado'] ?? 0) > 0)
                                            <div style="margin-top:3px; font-size:10px; font-weight:500; color:#64748b;">
                                                Abonado:
                                                $ {{ number_format(
                                                    $obligacion['valor_aplicado'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="4"
                                        style="height:120px; text-align:center; color:#94a3b8;"
                                    >
                                        @if(empty($estudianteSeleccionado))
                                            Seleccione un estudiante para consultar sus obligaciones.
                                        @elseif($tipoObligacionActiva === 'obligatorio')
                                            El estudiante no tiene obligaciones obligatorias pendientes.
                                        @else
                                            El estudiante no tiene conceptos no obligatorios pendientes.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse

                            @if(count($this->obligacionesVisibles) > 0)
                                <tr class="pagos-obligations-total">
                                    <td colspan="3">
                                        {{ $tipoObligacionActiva === 'obligatorio'
                                            ? 'Total obligatorios'
                                            : 'Total no obligatorios' }}
                                    </td>

                                    <td>
                                        $ {{ number_format(
                                            $this->totalObligacionesVisibles,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>

                

            </section>


            {{-- =====================================================
                6.2. HISTORIAL DE PAGOS
            ====================================================== --}}
            <section class="pagos-card pagos-panel pagos-info-card">

                <div class="pagos-panel-header">
                    <h2 class="pagos-panel-title">
                        4. Historial de pagos
                    </h2>

                    <button type="button" class="pagos-history-filter-button">
                        <x-heroicon-o-funnel />
                    </button>
                </div>

                {{-- Filtros rápidos --}}
                <div class="pagos-history-filters">

                    <div class="pagos-field">
                        <label>Buscar recibo</label>

                        <input
                            type="text"
                            class="pagos-input"
                            placeholder="N.º de recibo"
                            wire:model.live.debounce.400ms="filtroHistorialRecibo"
                        >
                    </div>

                    <div class="pagos-field">
                        <label>Buscar concepto</label>

                        <input
                            type="text"
                            class="pagos-input"
                            placeholder="Ej.: Pensión, matrícula..."
                            wire:model.live.debounce.400ms="filtroHistorialConcepto"
                        >
                    </div>

                    <div class="pagos-field">
                        <label>Desde</label>

                        <input
                            type="date"
                            class="pagos-input"
                            wire:model.live="filtroHistorialDesde"
                        >
                    </div>

                    <div class="pagos-field">
                        <label>Hasta</label>

                        <input
                            type="date"
                            class="pagos-input"
                            wire:model.live="filtroHistorialHasta"
                        >
                    </div>

                    <div class="pagos-field">
                        <label>Estado</label>

                        <select
                            class="pagos-select pagos-history-select"
                            wire:model.live="filtroHistorialEstado"
                        >
                            <option value="">Todos</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="anulado">Anulado</option>
                        </select>
                    </div>

                </div>

                {{-- Tabla --}}
                <div class="pagos-history-scroll">

                    <table class="pagos-table pagos-history-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Recibo</th>
                                <th>Concepto</th>
                                <th>Mes</th>
                                <th>Forma de pago</th>
                                <th>Valor pagado</th>
                                <th>Descuento</th>
                                <th>Recibido por</th>
                                <th>Estado</th>
                                <th>Acc.</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $grupoReciboAnterior = null;
                            @endphp

                            @forelse($historialPagos as $fila)
                                @php
                                    $esNuevoRecibo =
                                        $grupoReciboAnterior !== $fila['grupo_recibo'];
                                @endphp

                                @if($esNuevoRecibo)
                                    <tr class="pagos-history-receipt-group">
                                        <td colspan="10">
                                            <button
                                                type="button"
                                                class="pagos-history-receipt-button"
                                                wire:click="abrirDetalleRecibo({{ $fila['operacion_pago_id'] }})"
                                                title="Ver detalle completo del recibo"
                                            >
                                                <div class="pagos-history-receipt-group-content">
                                                    <div>
                                                        <strong>
                                                            Recibo N.º {{ $fila['numero_recibo'] }}
                                                        </strong>

                                                        <span>
                                                            {{ $fila['fecha_pago'] }}
                                                        </span>

                                                        <span>
                                                            Recibido por: {{ $fila['recibido_por'] }}
                                                        </span>
                                                    </div>

                                                    <div class="pagos-history-receipt-group-right">
                                                        @if($fila['estado'] === 'confirmado')
                                                            <span class="pagos-status pagos-status-confirmed">
                                                                Confirmado
                                                            </span>
                                                        @else
                                                            <span class="pagos-status pagos-status-cancelled">
                                                                Anulado
                                                            </span>
                                                        @endif

                                                        <x-heroicon-o-chevron-right />
                                                    </div>
                                                </div>
                                            </button>
                                        </td>
                                    </tr>
                                        
                                @endif

                                <tr
                                    wire:key="historial-pago-{{ $fila['id'] }}"
                                    @class([
                                        'pagos-history-row-cancelled' =>
                                            $fila['estado'] === 'anulado',
                                    ])
                                >
                                    <td>
                                        {{-- Fecha visible en el encabezado del recibo --}}
                                    </td>

                                    <td>
                                        {{-- Número visible en el encabezado del recibo --}}
                                    </td>

                                    <td>
                                        <div class="pagos-history-concept">
                                            <span>
                                                {{ $fila['concepto'] }}
                                            </span>

                                            @if($fila['es_obligatorio'])
                                                <span class="pagos-concept-badge pagos-concept-badge-required">
                                                    Obligatorio
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        {{ $fila['mes'] }}
                                    </td>

                                    <td>
                                        <div>
                                            {{ $fila['forma_pago'] }}

                                            @if(filled($fila['numero_referencia']))
                                                <small class="pagos-history-reference">
                                                    Ref: {{ $fila['numero_referencia'] }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        $ {{ number_format(
                                            $fila['valor_pagado'] ?? 0,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                    <td>
                                        $ {{ number_format(
                                            $fila['descuento'] ?? 0,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                    <td>
                                        {{ $fila['recibido_de'] }}
                                    </td>

                                    <td>
                                        {{-- Estado visible en el encabezado --}}
                                    </td>

                                    <td>
                                        <div class="pagos-table-actions">
                                            

                                            <button
                                                type="button"
                                                class="pagos-icon-button pagos-icon-button-print"
                                                title="Imprimir o reimprimir"
                                            >
                                                <x-heroicon-o-printer />
                                            </button>

                                            @if($fila['estado'] === 'confirmado')
                                                <button
                                                    type="button"
                                                    class="pagos-icon-button pagos-icon-button-danger"
                                                    title="Anular recibo"
                                                >
                                                    <x-heroicon-o-no-symbol />
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                @php
                                    $grupoReciboAnterior = $fila['grupo_recibo'];
                                @endphp
                            @empty
                                <tr>
                                    <td
                                        colspan="10"
                                        style="
                                            height: 155px;
                                            text-align: center;
                                            color: #94a3b8;
                                        "
                                    >
                                        @if($student_id)
                                            No se encontraron pagos para los filtros seleccionados.
                                        @else
                                            Seleccione un estudiante para consultar su historial de pagos.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>

                

                <div class="pagos-history-summary-row">
                    <span>Pagos</span>

                    <strong>
                        $ {{ number_format(
                            $resumenHistorial['pagos'] ?? 0,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <div class="pagos-history-summary-row">
                    <span>Descuentos</span>

                    <strong>
                        $ {{ number_format(
                            $resumenHistorial['descuentos'] ?? 0,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <div class="pagos-history-summary-total">
                    <span>Total pagado</span>

                    <strong>
                        $ {{ number_format(
                            $resumenHistorial['total_pagado'] ?? 0,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

            

            </section>

        </div>


        {{-- =========================================================
            7. ACUERDOS DE PAGO
        ========================================================== --}}
        <section class="pagos-card pagos-panel pagos-info-card pagos-agreements-card">

            <div class="pagos-panel-header">
                <h2 class="pagos-panel-title">
                    5. Acuerdos de pago
                </h2>

                <button type="button" class="pagos-agreement-add-button">
                    <x-heroicon-o-plus />
                    Nuevo acuerdo
                </button>
            </div>

            <div class="pagos-agreements-scroll">

                <table class="pagos-table pagos-agreements-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Realizado por</th>
                            <th>Resumen del acuerdo</th>
                            <th>Evidencias</th>
                            <th>Acc.</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>01/07/2026</td>
                            <td>Ana López</td>
                            <td>
                                <span class="pagos-agreement-summary">
                                    El acudiente se compromete a pagar la totalidad de la deuda al finalizar el mes...
                                </span>
                            </td>
                            <td>
                                <span class="pagos-evidence-badge">
                                    <x-heroicon-o-paper-clip />
                                    2 archivos
                                </span>
                            </td>
                            <td>
                                <div class="pagos-table-actions">

                                    <button
                                        type="button"
                                        class="pagos-icon-button pagos-icon-button-view"
                                        title="Ver acuerdo"
                                    >
                                        <x-heroicon-o-eye />
                                    </button>

                                    <button
                                        type="button"
                                        class="pagos-icon-button pagos-icon-button-edit"
                                        title="Editar acuerdo"
                                    >
                                        <x-heroicon-o-pencil-square />
                                    </button>

                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>15/06/2026</td>
                            <td>Juan Pérez</td>
                            <td>
                                <span class="pagos-agreement-summary">
                                    Se acuerda condonar los intereses de mora si el pago se realiza antes del 30 de junio...
                                </span>
                            </td>
                            <td>
                                <span class="pagos-evidence-badge">
                                    <x-heroicon-o-paper-clip />
                                    1 archivo
                                </span>
                            </td>
                            <td>
                                <div class="pagos-table-actions">

                                    <button
                                        type="button"
                                        class="pagos-icon-button pagos-icon-button-view"
                                        title="Ver acuerdo"
                                    >
                                        <x-heroicon-o-eye />
                                    </button>

                                    <button
                                        type="button"
                                        class="pagos-icon-button pagos-icon-button-edit"
                                        title="Editar acuerdo"
                                    >
                                        <x-heroicon-o-pencil-square />
                                    </button>

                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>20/05/2026</td>
                            <td>Ana López</td>
                            <td>
                                <span class="pagos-agreement-summary">
                                    Acuerdo de pago en dos cuotas para la salida pedagógica y los derechos de grado...
                                </span>
                            </td>
                            <td>
                                <span class="pagos-evidence-empty">
                                    Sin archivos
                                </span>
                            </td>
                            <td>
                                <div class="pagos-table-actions">

                                    <button
                                        type="button"
                                        class="pagos-icon-button pagos-icon-button-view"
                                        title="Ver acuerdo"
                                    >
                                        <x-heroicon-o-eye />
                                    </button>

                                    <button
                                        type="button"
                                        class="pagos-icon-button pagos-icon-button-edit"
                                        title="Editar acuerdo"
                                    >
                                        <x-heroicon-o-pencil-square />
                                    </button>

                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>10/04/2026</td>
                            <td>Carlos Gómez</td>
                            <td>
                                <span class="pagos-agreement-summary">
                                    El acudiente solicita prórroga hasta el día 15 de abril para cancelar la pensión...
                                </span>
                            </td>
                            <td>
                                <span class="pagos-evidence-badge">
                                    <x-heroicon-o-paper-clip />
                                    3 archivos
                                </span>
                            </td>
                            <td>
                                <div class="pagos-table-actions">

                                    <button
                                        type="button"
                                        class="pagos-icon-button pagos-icon-button-view"
                                        title="Ver acuerdo"
                                    >
                                        <x-heroicon-o-eye />
                                    </button>

                                    <button
                                        type="button"
                                        class="pagos-icon-button pagos-icon-button-edit"
                                        title="Editar acuerdo"
                                    >
                                        <x-heroicon-o-pencil-square />
                                    </button>

                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>

        </section>

       


        {{-- =========================================================
            8. MODALES
        ========================================================== --}}

        @if($mostrarDetalleRecibo)
            <div class="pagos-slideover-wrapper">

                <div
                    class="pagos-slideover-backdrop"
                    wire:click="cerrarDetalleRecibo"
                ></div>

                <aside class="pagos-slideover">

                    {{-- ENCABEZADO FIJO --}}
                    <div class="pagos-slideover-header">
                        <div>
                            <span class="pagos-slideover-eyebrow">
                                Detalle del recibo
                            </span>

                            <h2>Recibo</h2>
                        </div>

                        <button
                            type="button"
                            class="pagos-slideover-close"
                            wire:click="cerrarDetalleRecibo"
                            title="Cerrar"
                        >
                            <x-heroicon-o-x-mark />
                        </button>
                    </div>

                    {{-- ÚNICA ZONA CON SCROLL --}}
                    <div class="pagos-slideover-body">

                        @if(! empty($detalleRecibo))
                            <div class="pagos-receipt-detail">

                                {{-- RESUMEN GENERAL --}}
                                <section class="pagos-receipt-detail-summary">
                                    <div>
                                        <span>Recibo N.º</span>
                                        <strong>
                                            {{ $detalleRecibo['numero_recibo'] ?? '—' }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>Fecha</span>
                                        <strong>
                                            {{ $detalleRecibo['fecha'] ?? '—' }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>Estado</span>

                                        @if(($detalleRecibo['estado'] ?? '') === 'confirmada')
                                            <span class="pagos-status pagos-status-confirmed">
                                                Confirmado
                                            </span>
                                        @else
                                            <span class="pagos-status pagos-status-cancelled">
                                                {{ $detalleRecibo['estado_texto'] ?? 'Anulado' }}
                                            </span>
                                        @endif
                                    </div>
                                </section>

                                {{-- ESTUDIANTE --}}
                                <section class="pagos-receipt-detail-section">
                                    <h3>Estudiante</h3>

                                    <div class="pagos-receipt-detail-grid">
                                        <div>
                                            <span>Nombre</span>
                                            <strong>
                                                {{ $detalleRecibo['estudiante']['nombre'] ?? '—' }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>Código</span>
                                            <strong>
                                                {{ $detalleRecibo['estudiante']['codigo'] ?? '—' }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>Documento</span>
                                            <strong>
                                                {{ $detalleRecibo['estudiante']['documento'] ?? '—' }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>Curso</span>
                                            <strong>
                                                {{ $detalleRecibo['estudiante']['curso'] ?? '—' }}
                                            </strong>
                                        </div>
                                    </div>
                                </section>

                                {{-- INFORMACIÓN DEL RECAUDO --}}
                                <section class="pagos-receipt-detail-section">
                                    <h3>Información del recaudo</h3>

                                    <div class="pagos-receipt-detail-grid">
                                        <div>
                                            <span>Recibido de</span>
                                            <strong>
                                                {{ $detalleRecibo['recibido_de'] ?? '—' }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>Registrado por</span>
                                            <strong>
                                                {{ $detalleRecibo['registrado_por'] ?? '—' }}
                                            </strong>
                                        </div>
                                    </div>
                                </section>


                                



                                {{-- DETALLE DEL PAGO --}}
                                <section class="pagos-receipt-detail-section">
                                    <h3>Detalle del pago</h3>

                                    <div class="pagos-receipt-detail-lines">
                                        @foreach($detalleRecibo['lineas'] ?? [] as $linea)
                                            <article class="pagos-receipt-detail-line">

                                                <div class="pagos-receipt-detail-line-heading">
                                                    <div>
                                                        <strong>
                                                            {{ $linea['concepto'] }}
                                                        </strong>

                                                        @if(filled($linea['mes']))
                                                            <span>
                                                                {{ $linea['mes'] }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($linea['es_obligatorio'])
                                                        <span class="pagos-concept-badge pagos-concept-badge-required">
                                                            Obligatorio
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="pagos-receipt-detail-line-grid">
                                                    <div>
                                                        <span>Forma de pago</span>

                                                        <strong>
                                                            {{ $linea['forma_pago'] }}
                                                        </strong>

                                                        @if(filled($linea['numero_referencia']))
                                                            <small>
                                                                Ref. {{ $linea['numero_referencia'] }}
                                                            </small>
                                                        @endif

                                                        @if(filled($linea['fecha_consignacion']))
                                                            <small>
                                                                Fecha: {{ $linea['fecha_consignacion'] }}
                                                            </small>
                                                        @endif
                                                    </div>

                                                    <div>
                                                        <span>Valor recibido</span>
                                                        <strong>
                                                            $ {{ number_format(
                                                                $linea['valor_recibido'],
                                                                0,
                                                                ',',
                                                                '.'
                                                            ) }}
                                                        </strong>
                                                    </div>

                                                    <div>
                                                        <span>Descuento</span>
                                                        <strong>
                                                            $ {{ number_format(
                                                                $linea['descuento'],
                                                                0,
                                                                ',',
                                                                '.'
                                                            ) }}
                                                        </strong>
                                                    </div>

                                                    <div>
                                                        <span>Valor aplicado</span>
                                                        <strong>
                                                            $ {{ number_format(
                                                                $linea['valor_aplicado'],
                                                                0,
                                                                ',',
                                                                '.'
                                                            ) }}
                                                        </strong>
                                                    </div>

                                                    @if($linea['mostrar_saldo_pendiente'] ?? false)
                                                        <div>
                                                            <span>Saldo pendiente</span>
                                                            <strong class="pagos-text-warning">
                                                                $ {{ number_format(
                                                                    $linea['saldo_posterior'],
                                                                    0,
                                                                    ',',
                                                                    '.'
                                                                ) }}
                                                            </strong>
                                                        </div>
                                                    @endif

                                                    @if(($linea['saldo_favor_generado'] ?? 0) > 0)
                                                        <div>
                                                            <span>Saldo a favor</span>
                                                            <strong class="pagos-text-green">
                                                                $ {{ number_format(
                                                                    $linea['saldo_favor_generado'],
                                                                    0,
                                                                    ',',
                                                                    '.'
                                                                ) }}
                                                            </strong>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if(filled($linea['detalle']))
                                                    <div class="pagos-receipt-detail-note">
                                                        {{ $linea['detalle'] }}
                                                    </div>
                                                @endif

                                            </article>
                                        @endforeach
                                    </div>
                                </section>

                                {{-- TOTALES --}}
                                <section class="pagos-receipt-detail-totals">
                                    <div>
                                        <span>Pagos recibidos</span>
                                        <strong>
                                            $ {{ number_format(
                                                $detalleRecibo['total_recibido'] ?? 0,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>Descuentos</span>
                                        <strong>
                                            $ {{ number_format(
                                                $detalleRecibo['total_descuentos'] ?? 0,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </strong>
                                    </div>

                                    @if(($detalleRecibo['saldo_favor_generado'] ?? 0) > 0)
                                        <div>
                                            <span>Saldo a favor generado</span>
                                            <strong class="pagos-text-green">
                                                $ {{ number_format(
                                                    $detalleRecibo['saldo_favor_generado'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </strong>
                                        </div>
                                    @endif

                                    <div class="pagos-receipt-detail-total">
                                        <span>Total recibido</span>
                                        <strong>
                                            $ {{ number_format(
                                                $detalleRecibo['total_recibido'] ?? 0,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </strong>
                                    </div>
                                </section>

                                <section class="pagos-receipt-detail-section">
                                    <h3>Historial de impresión</h3>

                                    <div class="pagos-print-history">

                                        @if(
                                            $detalleRecibo['impresion']['ha_sido_impreso']
                                            ?? false
                                        )
                                            <div class="pagos-print-history-item">
                                                <div>
                                                    <span>Impresión original</span>
                                                    <strong>
                                                        Recibo N.º
                                                        {{ $detalleRecibo['numero_recibo'] }}
                                                    </strong>
                                                </div>

                                                <div>
                                                    <span>Fecha y hora</span>
                                                    <strong>
                                                        {{
                                                            $detalleRecibo['impresion']['original']['fecha']
                                                            ?? '—'
                                                        }}
                                                    </strong>
                                                </div>

                                                <div>
                                                    <span>Usuario</span>
                                                    <strong>
                                                        {{
                                                            $detalleRecibo['impresion']['original']['usuario']
                                                            ?? '—'
                                                        }}
                                                    </strong>
                                                </div>
                                            </div>

                                            @if(
                                                ($detalleRecibo['impresion']['cantidad_reimpresiones']
                                                    ?? 0) > 0
                                            )
                                                @php
                                                    $ultimaReimpresion =
                                                        $detalleRecibo['impresion']['ultima_reimpresion'];

                                                    $identificadorUltima =
                                                        ($detalleRecibo['numero_recibo'] ?? '')
                                                        . '-R'
                                                        . ($ultimaReimpresion['numero_reimpresion'] ?? '');
                                                @endphp

                                                <div class="pagos-print-history-item">
                                                    <div>
                                                        <span>Última reimpresión</span>
                                                        <strong>
                                                            {{ $identificadorUltima }}
                                                        </strong>
                                                    </div>

                                                    <div>
                                                        <span>Fecha y hora</span>
                                                        <strong>
                                                            {{ $ultimaReimpresion['fecha'] ?? '—' }}
                                                        </strong>
                                                    </div>

                                                    <div>
                                                        <span>Usuario</span>
                                                        <strong>
                                                            {{ $ultimaReimpresion['usuario'] ?? '—' }}
                                                        </strong>
                                                    </div>

                                                    @if(filled($ultimaReimpresion['motivo'] ?? null))
                                                        <div class="pagos-print-history-reason">
                                                            <span>Motivo</span>
                                                            <strong>
                                                                {{ $ultimaReimpresion['motivo'] }}
                                                            </strong>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="pagos-print-history-total">
                                                <span>Total de reimpresiones</span>

                                                <strong>
                                                    {{
                                                        $detalleRecibo['impresion']['cantidad_reimpresiones']
                                                        ?? 0
                                                    }}
                                                </strong>
                                            </div>
                                        @else
                                            <div class="pagos-print-history-empty">
                                                Este recibo todavía no tiene una impresión original registrada.
                                            </div>
                                        @endif

                                    </div>
                                </section>

                            </div>
                        @else
                            <div class="pagos-slideover-loading">
                                No se encontró información del recibo.
                            </div>
                        @endif

                    </div>
                    {{-- FIN pagos-slideover-body --}}

                    {{-- PIE FIJO: debe estar fuera del body --}}
                    <div class="pagos-slideover-footer">

                        <button
                            type="button"
                            class="
                                pagos-slideover-footer-button
                                pagos-slideover-footer-button-secondary
                            "
                            wire:click="cerrarDetalleRecibo"
                        >
                            Cerrar
                        </button>

                        @if(
                            ! ($detalleRecibo['impresion']['ha_sido_impreso'] ?? false)
                        )
                            <button
                                type="button"
                                class="
                                    pagos-slideover-footer-button
                                    pagos-slideover-footer-button-primary
                                "
                                wire:click="imprimirRecibo"
                                wire:loading.attr="disabled"
                                wire:target="imprimirRecibo"
                            >
                                <x-heroicon-o-printer />

                                <span wire:loading.remove wire:target="imprimirRecibo">
                                    Imprimir recibo
                                </span>

                                <span wire:loading wire:target="imprimirRecibo">
                                    Registrando...
                                </span>
                            </button>
                        @else
                            <button
                                type="button"
                                class="
                                    pagos-slideover-footer-button
                                    pagos-slideover-footer-button-primary
                                "
                                wire:click="abrirModalReimpresion"
                            >
                                <x-heroicon-o-printer />
                                Reimprimir recibo
                            </button>
                        @endif

                    </div>

                </aside>
            </div>
        @endif











        @if($mostrarModalReimpresion)
            <div class="pagos-modal-backdrop">

                <div
                    class="pagos-modal-card"
                    wire:click.stop
                >
                    <div class="pagos-modal-header">
                        <div>
                            <span>Reimpresión</span>

                            <h3>
                                Recibo N.º
                                {{ $detalleRecibo['numero_recibo'] ?? '—' }}
                            </h3>
                        </div>

                        <button
                            type="button"
                            class="pagos-slideover-close"
                            wire:click="cerrarModalReimpresion"
                        >
                            <x-heroicon-o-x-mark />
                        </button>
                    </div>

                    <div class="pagos-modal-body">
                        <div class="pagos-field">
                            <label>Motivo de la reimpresión (opcional)</label>

                            <textarea
                                class="pagos-textarea"
                                rows="4"
                                wire:model="motivoReimpresion"
                                placeholder="Ej.: Recibo extraviado por el acudiente."
                            ></textarea>
                        </div>

                        <div class="pagos-reprint-preview">
                            La copia se identificará como:

                            <strong>
                                {{ $detalleRecibo['numero_recibo'] ?? '' }}-R{{
                                    ($detalleRecibo['impresion']['cantidad_reimpresiones']
                                        ?? 0) + 1
                                }}
                            </strong>
                        </div>
                    </div>

                    <div class="pagos-modal-footer">
                        <button
                            type="button"
                            class="
                                pagos-slideover-footer-button
                                pagos-slideover-footer-button-secondary
                            "
                            wire:click="cerrarModalReimpresion"
                        >
                            Cancelar
                        </button>

                        <button
                            type="button"
                            class="
                                pagos-slideover-footer-button
                                pagos-slideover-footer-button-primary
                            "
                            wire:click="reimprimirRecibo"
                            wire:loading.attr="disabled"
                            wire:target="reimprimirRecibo"
                        >
                            <x-heroicon-o-printer />

                            <span
                                wire:loading.remove
                                wire:target="reimprimirRecibo"
                            >
                                Generar reimpresión
                            </span>

                            <span
                                wire:loading
                                wire:target="reimprimirRecibo"
                            >
                                Registrando...
                            </span>
                        </button>
                    </div>
                </div>

            </div>
        @endif


    </div>

</x-filament-panels::page>