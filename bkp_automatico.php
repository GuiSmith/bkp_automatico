<?php
// Configurações
$repo_dir = getenv("HOME") . '/backup_cartoes';
$branch = 'master';
$log_file = $repo_dir . '/backup_gitlab.log';

// Função para escrever no log
function write_log($message, $log_file) {
    $timestamp = date('d/m/Y H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Início do processo
write_log("-------------------------", $log_file);
write_log("Início do backup", $log_file);

// Verifica se o diretório existe
if (!is_dir($repo_dir)) {
    write_log("Erro: Pasta $repo_dir não encontrada!", $log_file);
    exit(1);
}

// Executa comandos Git
chdir($repo_dir);

// Adiciona mudanças
exec('git add .', $output_add, $return_add);

// Verifica se há mudanças
exec('git diff-index --quiet HEAD --', $output_diff, $return_diff);

if ($return_diff !== 0) { // Há mudanças
    $commit_message = "Backup automático: " . date('d/m/Y H:i:s');
    exec("git commit -m \"$commit_message\"", $output_commit, $return_commit);
    
    if ($return_commit === 0) {
        exec("git push origin $branch", $output_push, $return_push);
        if ($return_push === 0) {
            write_log("Backup enviado com sucesso.", $log_file);
        } else {
            write_log("Erro ao enviar backup para o GitLab.", $log_file);
        }
    } else {
        write_log("Erro ao fazer commit.", $log_file);
    }
} else {
    write_log("Nenhuma alteração detectada. Nenhum commit/push necessário.", $log_file);
}

write_log("Fim do backup", $log_file);
write_log("-------------------------", $log_file);
