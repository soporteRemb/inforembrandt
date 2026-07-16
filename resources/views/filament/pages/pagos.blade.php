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
        .pagos-info-grid {
            display: grid;
            grid-template-columns: minmax(620px, 1.2fr) minmax(520px, 1fr);
            gap: 14px;
            align-items: stretch;
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
                minmax(130px, .9fr)
                minmax(125px, .8fr)
                minmax(125px, .8fr)
                minmax(130px, .9fr);
            gap: 8px;
            padding: 10px 12px;
            border-bottom: 1px solid #e5eaf0;
            background: #fbfcfd;
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

                                <select class="pagos-select">
                                    <option value="">Seleccione una obligación</option>
                                    <option value="pension-febrero">Pensión - Febrero</option>
                                    <option value="matricula">Matrícula</option>
                                    <option value="salida-pedagogica">Salida pedagógica</option>
                                    <option value="anticipo-general">Anticipo general</option>
                                </select>
                            </div>

                            {{-- Mes o periodo --}}
                            <div class="pagos-field">
                                <label>Mes / Periodo</label>

                                <select class="pagos-select">
                                    <option value="">Seleccione</option>
                                    <option value="febrero">Febrero</option>
                                    <option value="marzo">Marzo</option>
                                    <option value="abril">Abril</option>
                                    <option value="anual">Anual</option>
                                </select>
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
                                        class="pagos-input pagos-input-money"
                                        value="$ 120.000"
                                    >
                                </div>

                                <div class="pagos-field">
                                    <label>Descuento</label>

                                    <input
                                        type="text"
                                        class="pagos-input pagos-input-money"
                                        value="$ 0"
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

                                    <select class="pagos-select">
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="consignacion">Consignación</option>
                                        <option value="nequi">Nequi</option>
                                        <option value="daviplata">Daviplata</option>
                                    </select>
                                </div>

                                <div class="pagos-field">
                                    <label>Referencia / Consignación</label>

                                    <input
                                        type="text"
                                        class="pagos-input"
                                        placeholder="-"
                                    >
                                </div>

                                <div class="pagos-field">
                                    <label>Fecha</label>

                                    <input
                                        type="date"
                                        class="pagos-input"
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
                                        <strong>Ana López</strong>
                                        
                                    </div>

                                    <span class="pagos-received-date">
                                        14/07/2026 · 02:33 p. m.
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
                                    placeholder="Ej.: Abono, acuerdo verbal, anticipo, etc."
                                >
                            </div>

                            {{-- Resumen de esta transacción --}}
                            <div class="pagos-transaction-summary">

                                <div class="pagos-transaction-summary-row">
                                    <span>Valor a pagar</span>
                                    <strong>$ 120.000</strong>
                                </div>

                                <div class="pagos-transaction-summary-row">
                                    <span>Descuento</span>
                                    <strong>$ 0</strong>
                                </div>

                                <div class="pagos-transaction-summary-row pagos-transaction-total">
                                    <span>Total recibido</span>
                                    <strong>$ 120.000</strong>
                                </div>

                            </div>

                            <button type="button" class="pagos-btn-add-queue">
                                <x-heroicon-o-shopping-cart />
                                Adicionar este pago a la cola
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
                            <strong>1258</strong>
                        </span>
                    </div>

                    <button type="button" class="pagos-clear-button">
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
                            <tr>
                                
                                <td>Pensión</td>
                                <td>Febrero</td>
                                
                                <td>$ 0</td>
                                <td>$ 120.000</td>
                                <td>Efectivo (1)</td>
                                <td>
                                    <div class="pagos-table-actions">
                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-edit"
                                        >
                                            <x-heroicon-o-pencil-square />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-danger"
                                        >
                                            <x-heroicon-o-trash />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                
                                <td>Guías</td>
                                <td>Anual</td>
                                
                                <td>$ 0</td>
                                <td>$ 80.000</td>
                                <td>Nequi (1)</td>
                                <td>
                                    <div class="pagos-table-actions">
                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-edit"
                                        >
                                            <x-heroicon-o-pencil-square />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-danger"
                                        >
                                            <x-heroicon-o-trash />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                               
                                <td>Salida pedagógica</td>
                                <td>Junio</td>
                                
                                <td>$ 0</td>
                                <td>$ 100.000</td>
                                <td>Transferencia (1)</td>
                                <td>
                                    <div class="pagos-table-actions">
                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-edit"
                                        >
                                            <x-heroicon-o-pencil-square />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-danger"
                                        >
                                            <x-heroicon-o-trash />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <div class="pagos-queue-summary">
                    <div class="pagos-queue-summary-row">
                        <span>Subtotal</span>
                        <strong>$ 300.000</strong>
                    </div>

                    <div class="pagos-queue-summary-row">
                        <span>Descuentos</span>
                        <strong>$ 0</strong>
                    </div>

                    <div class="pagos-queue-total">
                        <span>Total a recibir</span>
                        <strong>$ 300.000</strong>
                    </div>

                    <button type="button" class="pagos-confirm-button">
                        <x-heroicon-o-check />
                        Confirmar y registrar pagos
                    </button>
                </div>

            </section>
            </div>


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
                    <button type="button" class="pagos-tab pagos-tab-active">
                        Obligatorios (5)
                    </button>

                    <button type="button" class="pagos-tab">
                        No obligatorios (3)
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
                            <tr>
                                <td>Pensión</td>
                                <td>Febrero</td>
                                <td>10/02/2026</td>
                                <td>$ 120.000</td>
                            </tr>

                            <tr>
                                <td>Pensión</td>
                                <td>Marzo</td>
                                <td>10/03/2026</td>
                                <td>$ 120.000</td>
                            </tr>

                            <tr>
                                <td>Pensión</td>
                                <td>Abril</td>
                                <td>10/04/2026</td>
                                <td>$ 130.000</td>
                            </tr>

                            <tr>
                                <td>Matrícula</td>
                                <td>Anual</td>
                                <td>31/01/2026</td>
                                <td>$ 250.000</td>
                            </tr>

                            <tr>
                                <td>Guías</td>
                                <td>Anual</td>
                                <td>31/03/2026</td>
                                <td>$ 80.000</td>
                            </tr>

                            <tr class="pagos-obligations-total">
                                <td colspan="3">Total obligatorios</td>
                                <td>$ 700.000</td>
                            </tr>
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
                        >
                    </div>

                    <div class="pagos-field">
                        <label>Desde</label>

                        <input
                            type="date"
                            class="pagos-input"
                        >
                    </div>

                    <div class="pagos-field">
                        <label>Hasta</label>

                        <input
                            type="date"
                            class="pagos-input"
                        >
                    </div>

                    <div class="pagos-field">
                        <label>Estado</label>

                        <select class="pagos-select pagos-history-select">
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

                            <tr>
                                <td>14/07/2026 02:33 p. m.</td>
                                <td>
                                    <strong class="pagos-history-receipt">
                                        1258
                                    </strong>
                                </td>
                                <td>Pensión</td>
                                <td>Febrero</td>
                                <td>Efectivo</td>
                                <td>$ 120.000</td>
                                <td>$ 0</td>
                                <td>Ana López</td>
                                <td>
                                    <span class="pagos-status pagos-status-confirmed">
                                        Confirmado
                                    </span>
                                </td>
                                <td>
                                    <div class="pagos-table-actions">

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-view"
                                            title="Ver detalle"
                                        >
                                            <x-heroicon-o-eye />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-print"
                                            title="Imprimir o reimprimir"
                                        >
                                            <x-heroicon-o-printer />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-danger"
                                            title="Anular recibo"
                                        >
                                            <x-heroicon-o-no-symbol />
                                        </button>

                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>25/06/2026 03:40 p. m.</td>
                                <td>
                                    <strong class="pagos-history-receipt">
                                        1221
                                    </strong>
                                </td>
                                <td>Matrícula</td>
                                <td>Anual</td>
                                <td>Transferencia</td>
                                <td>$ 230.000</td>
                                <td>$ 20.000</td>
                                <td>Juan Pérez</td>
                                <td>
                                    <span class="pagos-status pagos-status-confirmed">
                                        Confirmado
                                    </span>
                                </td>
                                <td>
                                    <div class="pagos-table-actions">

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-view"
                                            title="Ver detalle"
                                        >
                                            <x-heroicon-o-eye />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-print"
                                            title="Imprimir o reimprimir"
                                        >
                                            <x-heroicon-o-printer />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-danger"
                                            title="Anular recibo"
                                        >
                                            <x-heroicon-o-no-symbol />
                                        </button>

                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>18/06/2026 09:20 a. m.</td>
                                <td>
                                    <strong class="pagos-history-receipt">
                                        1198
                                    </strong>
                                </td>
                                <td>Salida pedagógica</td>
                                <td>Mayo</td>
                                <td>Nequi</td>
                                <td>$ 90.000</td>
                                <td>$ 0</td>
                                <td>Ana López</td>
                                <td>
                                    <span class="pagos-status pagos-status-confirmed">
                                        Confirmado
                                    </span>
                                </td>
                                <td>
                                    <div class="pagos-table-actions">

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-view"
                                            title="Ver detalle"
                                        >
                                            <x-heroicon-o-eye />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-print"
                                            title="Imprimir o reimprimir"
                                        >
                                            <x-heroicon-o-printer />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-danger"
                                            title="Anular recibo"
                                        >
                                            <x-heroicon-o-no-symbol />
                                        </button>

                                    </div>
                                </td>
                            </tr>

                            <tr class="pagos-history-row-cancelled">
                                <td>05/06/2026 11:05 a. m.</td>
                                <td>
                                    <strong class="pagos-history-receipt">
                                        1175
                                    </strong>
                                </td>
                                <td>Pensión</td>
                                <td>Enero</td>
                                <td>Efectivo</td>
                                <td>$ 120.000</td>
                                <td>$ 0</td>
                                <td>Ana López</td>
                                <td>
                                    <span class="pagos-status pagos-status-cancelled">
                                        Anulado
                                    </span>
                                </td>
                                <td>
                                    <div class="pagos-table-actions">

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-view"
                                            title="Ver detalle"
                                        >
                                            <x-heroicon-o-eye />
                                        </button>

                                        <button
                                            type="button"
                                            class="pagos-icon-button pagos-icon-button-print"
                                            title="Imprimir comprobante"
                                        >
                                            <x-heroicon-o-printer />
                                        </button>

                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>

                </div>

                

                <div class="pagos-history-summary-row">
                    <span>Pagos</span>
                    <strong>$ 4.150.000</strong>
                </div>

                <div class="pagos-history-summary-row">
                    <span>Descuentos</span>
                    <strong>$ 180.000</strong>
                </div>

                <div class="pagos-history-summary-total">
                    <span>Total pagado</span>
                    <strong>$ 3.970.000</strong>
                </div>

            

            </section>

        </div>


        {{-- =========================================================
            7. ACUERDOS DE PAGO
        ========================================================== --}}
        <section class="pagos-card pagos-panel pagos-info-card">

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

    </div>

</x-filament-panels::page>