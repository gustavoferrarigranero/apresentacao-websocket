# Testes e demonstração — Real-time com WebSockets

## Preparação

```bash
nvm use
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan test
```

O `.env.example` já contém a configuração local do Reverb.

## Processos necessários

Execute em terminais separados:

```bash
php artisan serve
php artisan reverb:start --debug
php artisan queue:work --tries=3
npm run dev
```

## Dashboard de demonstração

Abra:

```text
http://127.0.0.1:8000/demo/realtime/1
```

Essa rota existe apenas nos ambientes `local` e `testing`. Ela autentica o utilizador criado pelo seed e apresenta a agenda da clínica 1.

Abra a mesma página em duas janelas para demonstrar o contador do canal de presença.

## Criação em tempo real

Em outro terminal, publique um agendamento do seed para observar a atualização nas duas janelas:

```bash
php artisan tinker --execute="$appointment=App\Models\Appointment::where('clinic_id',1)->first(); App\Events\AppointmentCreated::dispatch($appointment);"
```

Para a demonstração automatizada do fluxo de criação, use:

```bash
php artisan test --filter=creation_broadcasts
```

O teste comprova que a criação publica `appointment.created` e que um agendamento urgente gera notificação por database, broadcast e mail.

## Alteração de status

```bash
php artisan test --filter=status_update_dispatches
```

O evento `.appointment.status.changed` atualiza o item existente no dashboard sem criar uma segunda linha.

## Cancelamento

```bash
php artisan test --filter=cancellation_broadcasts
```

O teste confirma o evento `.appointment.cancelled`, o novo estado e a proibição de acesso entre clínicas.

## Canais privados e presença

```bash
php artisan channel:list
php artisan route:list --path=broadcasting
php artisan test --filter=private_and_presence
```

Resultados esperados:

- `clinic.{clinicId}` retorna `true` somente para a clínica do utilizador;
- `agenda.{clinicId}` retorna apenas `id` e `name`;
- outro tenant recebe negação;
- `/broadcasting/auth` está registrado com middleware web.

## Estado previsível no frontend

O arquivo `resources/js/clinic-dashboard.js` demonstra:

- mapa de elementos indexado pelo ID do agendamento;
- inserção somente quando o ID ainda não existe;
- atualização do estado para eventos repetidos;
- contador de utilizadores conectados;
- saída dos dois canais no evento `beforeunload`.

Valide a compilação:

```bash
npm run build
```

## Notificação multicanal

`UrgentAppointment` centraliza a mensagem e utiliza:

- `database` para histórico;
- `broadcast` para a interface ao vivo;
- `mail` para utilizadores fora do sistema.

Valide com:

```bash
php artisan test --filter=urgent_notification
```

## Verificação completa

```bash
composer validate --no-check-publish
vendor/bin/pint --test
php artisan migrate:fresh --seed
php artisan test
npm run build
php artisan route:list --path=api
php artisan route:list --path=broadcasting
php artisan channel:list
```

O projeto está pronto quando os testes passam, o frontend compila, o Reverb inicia na porta 8080 e o dashboard reage sem recarregar a página.
