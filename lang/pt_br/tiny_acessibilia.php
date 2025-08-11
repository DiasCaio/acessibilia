<?php
// This file is part of Moodle - http://moodle.org/
// GNU GPL v3 or later.

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Acessibilia';

// UI (botão/menubar/diálogo)
$string['buttontitle'] = 'Acessibilia: gerar descrição de imagem';
$string['menulabel'] = 'Acessibilia';
$string['dialogtitle'] = 'Acessibilia — descrição de imagem';
$string['selectfile'] = 'Selecionar arquivo ou arrastar aqui';
$string['generate'] = 'Gerar descrição';
$string['regenerate'] = 'Gerar novamente';
$string['insert'] = 'Inserir';
$string['cancel'] = 'Cancelar';
$string['altlabel'] = 'Descrição (alt) da imagem';
$string['decorative'] = 'Imagem decorativa (alt vazio)';
$string['error_nofile'] = 'Selecione uma imagem para continuar.';
$string['error_unsupported'] = 'Arquivo não suportado ou operação indisponível.';
$string['error_upload'] = 'Falha ao enviar a imagem. Tente novamente.';
$string['error_ia'] = 'Não foi possível gerar a descrição automaticamente.';
$string['notice_privacy'] = 'A imagem selecionada será enviada a um serviço externo para gerar a descrição.';

// Settings (admin)
$string['setting:enabled'] = 'Ativar Acessibilia';
$string['setting:enabled_desc'] = 'Habilita o plugin Acessibilia no editor TinyMCE.';
$string['setting:apiurl'] = 'URL da API';
$string['setting:apiurl_desc'] = 'Endpoint do serviço de IA (Google AI Studio).';
$string['setting:model'] = 'Modelo';
$string['setting:model_desc'] = 'Nome do modelo a ser utilizado (ex.: gemini-2.0-flash).';
$string['setting:apikey'] = 'Chave da API';
$string['setting:apikey_desc'] = 'Chave privada do serviço de IA (armazenada no servidor; não é exposta ao navegador).';
$string['setting:prompt'] = 'Prompt padrão';
$string['setting:prompt_desc'] = 'Prompt base em português para orientar a geração da descrição.';
$string['setting:timeout'] = 'Tempo limite (ms)';
$string['setting:timeout_desc'] = 'Tempo máximo de espera pela resposta da IA.';
$string['setting:forcealt'] = 'Exigir alt não vazio';
$string['setting:forcealt_desc'] = 'Exige que a descrição (alt) seja preenchida, exceto quando marcado como imagem decorativa.';
$string['setting:allowdecorative'] = 'Permitir imagem decorativa';
$string['setting:allowdecorative_desc'] = 'Permite inserir imagens com alt vazio (decorativas).';
$string['setting:logs'] = 'Ativar logs do servidor';
$string['setting:logs_desc'] = 'Envia metadados de uso (sem conteúdo) para o servidor para fins de diagnóstico.';

// Capability
$string['acessibilia:use'] = 'Usar o plugin Acessibilia';

// Privacy (null provider)
$string['privacy:metadata'] = 'O plugin Acessibilia não armazena quaisquer dados pessoais.';
