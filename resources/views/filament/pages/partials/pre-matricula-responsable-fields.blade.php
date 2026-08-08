<div class="pre-detail-field">
    <label>Nombre completo</label>

    <input
        type="text"
        class="pre-detail-input"
        wire:model.defer="formularioEdicion.{{ $prefijo }}_nombre"
    >
</div>

<div class="pre-detail-field">
    <label>Teléfono</label>

    <input
        type="text"
        class="pre-detail-input"
        wire:model.defer="formularioEdicion.{{ $prefijo }}_telefono"
    >
</div>

<div class="pre-detail-field">
    <label>Tipo de documento</label>

    <select
        class="pre-detail-select"
        wire:model.defer="formularioEdicion.{{ $prefijo }}_tipo_documento"
    >
        <option value="">Seleccione</option>
        <option value="CC">Cédula de ciudadanía</option>
        <option value="CE">Cédula de extranjería</option>
        <option value="PA">Pasaporte</option>
    </select>
</div>

<div class="pre-detail-field">
    <label>Documento</label>

    <input
        type="text"
        class="pre-detail-input"
        wire:model.defer="formularioEdicion.{{ $prefijo }}_documento"
    >
</div>

<div class="pre-detail-field">
    <label>Lugar de trabajo</label>

    <input
        type="text"
        class="pre-detail-input"
        wire:model.defer="formularioEdicion.{{ $prefijo }}_lugar_trabajo"
    >
</div>

<div class="pre-detail-field">
    <label>Correo electrónico</label>

    <input
        type="email"
        class="pre-detail-input"
        wire:model.defer="formularioEdicion.{{ $prefijo }}_correo"
    >
</div>

<div class="pre-detail-field pre-detail-field-full">
    <label>Dirección</label>

    <input
        type="text"
        class="pre-detail-input"
        wire:model.defer="formularioEdicion.{{ $prefijo }}_direccion"
    >
</div>