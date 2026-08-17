@extends('layouts.app')

@section('title', 'Cadastrar justificativa')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
    <style>#cropImage { display: block; max-width: 100%; max-height: 65vh; }</style>
@endpush

@section('content')
    <div class="page-header"><div><h1 class="page-title">Cadastrar justificativa</h1><p class="page-description">Informe os dados e anexe o documento comprobatório.</p></div></div>
    <form id="justificativaForm" method="POST" action="{{ route('justificativas.store') }}" enctype="multipart/form-data">@csrf
        <div class="card content-card">
            <div class="card-header"><h5>Dados da justificativa</h5></div>
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>@endif
                <div class="row g-3">
                    <div class="col-12"><label for="descricao" class="form-label">Descrição</label><textarea id="descricao" name="descricao" rows="5" class="form-control @error('descricao') is-invalid @enderror" required autofocus>{{ old('descricao') }}</textarea>@error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label for="colaborador" class="form-label">Colaborador</label><input id="colaborador" class="form-control" value="{{ $colaborador->nome }}" disabled></div>
                    <div class="col-md-6"><label for="categoria_id" class="form-label">Categoria</label><select id="categoria_id" name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required><option value="">Selecione</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}" @selected((string) old('categoria_id') === (string) $categoria->id)>{{ $categoria->nome }}</option>@endforeach</select>@error('categoria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label for="anexo" class="form-label">Anexo</label><input type="file" id="anexo" name="anexo" class="form-control @error('anexo') is-invalid @enderror" accept="application/pdf,image/jpeg,image/png,image/webp" required><div class="form-text">Envie um PDF ou uma foto (JPG, PNG ou WebP) de até 10 MB. Fotos poderão ser recortadas antes do envio.</div>@error('anexo')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><input type="hidden" name="atestado_medico" value="0"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="atestado_medico" name="atestado_medico" value="1" @checked(old('atestado_medico') === '1')><label class="form-check-label fw-semibold" for="atestado_medico">Este anexo é um atestado médico de tratamento de saúde? <span id="atestadoResposta" class="text-muted">Não</span></label></div></div>
                    <div id="tipoAtestadoBloco" class="col-12 d-none"><fieldset><legend class="form-label fs-6">É um atestado próprio ou de acompanhamento de familiar?</legend><div class="d-flex flex-wrap gap-4"><div class="form-check"><input class="form-check-input" type="radio" name="tipo_atestado" id="tipo_proprio" value="proprio" @checked(old('tipo_atestado') === 'proprio')><label class="form-check-label" for="tipo_proprio">Atestado próprio</label></div><div class="form-check"><input class="form-check-input" type="radio" name="tipo_atestado" id="tipo_acompanhamento" value="acompanhamento" @checked(old('tipo_atestado') === 'acompanhamento')><label class="form-check-label" for="tipo_acompanhamento">Acompanhamento de familiar</label></div></div></fieldset></div>
                    <div id="parentescoBloco" class="col-md-6 d-none"><label for="grau_parentesco_id" class="form-label">Grau de parentesco da pessoa acompanhada</label><select id="grau_parentesco_id" name="grau_parentesco_id" class="form-select @error('grau_parentesco_id') is-invalid @enderror"><option value="">Selecione</option>@foreach($grausParentesco as $grau)<option value="{{ $grau->id }}" @selected((string) old('grau_parentesco_id') === (string) $grau->id)>{{ $grau->nome }}</option>@endforeach</select>@error('grau_parentesco_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('justificativas.index') }}" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar</button></div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="cropModalLabel">Recortar foto</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancelar"></button></div><div class="modal-body bg-dark d-flex justify-content-center"><img id="cropImage" alt="Pré-visualização da foto"></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" id="confirmarRecorte" class="btn btn-primary"><i class="bi bi-crop me-1"></i>Usar recorte</button></div></div></div></div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const atestado = document.getElementById('atestado_medico');
            const resposta = document.getElementById('atestadoResposta');
            const tipoBloco = document.getElementById('tipoAtestadoBloco');
            const parentescoBloco = document.getElementById('parentescoBloco');
            const parentesco = document.getElementById('grau_parentesco_id');
            const tipos = [...document.querySelectorAll('input[name="tipo_atestado"]')];
            const atualizarPerguntas = () => {
                const acompanhamento = document.getElementById('tipo_acompanhamento').checked;
                resposta.textContent = atestado.checked ? 'Sim' : 'Não';
                tipoBloco.classList.toggle('d-none', !atestado.checked);
                parentescoBloco.classList.toggle('d-none', !atestado.checked || !acompanhamento);
                tipos.forEach(tipo => tipo.required = atestado.checked);
                parentesco.required = atestado.checked && acompanhamento;
                if (!atestado.checked) tipos.forEach(tipo => tipo.checked = false);
                if (!atestado.checked || !acompanhamento) parentesco.value = '';
            };
            atestado.addEventListener('change', atualizarPerguntas);
            tipos.forEach(tipo => tipo.addEventListener('change', atualizarPerguntas));
            atualizarPerguntas();

            const input = document.getElementById('anexo');
            const imagem = document.getElementById('cropImage');
            const modalElement = document.getElementById('cropModal');
            const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });
            let cropper = null;
            let recorteConfirmado = false;
            input.addEventListener('change', () => {
                const arquivo = input.files[0];
                recorteConfirmado = !arquivo?.type.startsWith('image/');
                if (!arquivo || recorteConfirmado) return;
                imagem.src = URL.createObjectURL(arquivo);
                modal.show();
            });
            modalElement.addEventListener('shown.bs.modal', () => {
                cropper?.destroy();
                cropper = new Cropper(imagem, { viewMode: 1, autoCropArea: 1, responsive: true });
            });
            document.getElementById('confirmarRecorte').addEventListener('click', () => {
                cropper.getCroppedCanvas({ maxWidth: 2400, maxHeight: 2400, imageSmoothingQuality: 'high' }).toBlob(blob => {
                    const nome = input.files[0].name.replace(/\.[^.]+$/, '') + '-recortada.jpg';
                    const transfer = new DataTransfer();
                    transfer.items.add(new File([blob], nome, { type: 'image/jpeg' }));
                    input.files = transfer.files;
                    recorteConfirmado = true;
                    modal.hide();
                }, 'image/jpeg', 0.9);
            });
            modalElement.addEventListener('hidden.bs.modal', () => {
                cropper?.destroy(); cropper = null;
                if (!recorteConfirmado) input.value = '';
                if (imagem.src.startsWith('blob:')) URL.revokeObjectURL(imagem.src);
            });
        });
    </script>
@endpush
