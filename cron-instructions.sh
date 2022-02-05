# realizar sincronização (API de Integração -> Sincronizar) diariamente as 03:00 horas
00 03 * * * /bin/bash -l -c 'cd /var/www/idiario && RAILS_ENV=production bundle exec rake ieducar_api:synchronize' 2>&1
# realizar atualização das informações do acompanhamento pedagógico diariamente as 03:10 horas
10 03 * * * /bin/bash -l -c 'cd /var/www/idiario && RAILS_ENV=production bundle exec rake refresh_pedagogical_tracking_views' 2>&1
