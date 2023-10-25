docker rmi $(docker images -a -q)
docker login registry.gitlab.com/sprint-technology/watthelp/api
docker build --pull --no-cache docker -t registry.gitlab.com/sprint-technology/watthelp/api/php:7.4
docker push registry.gitlab.com/sprint-technology/watthelp/api/php:7.4

docker rmi $(docker images -a -q)
docker login registry.gitlab.com/sprint-technology/watthelp/api
docker build --pull --no-cache docker -t registry.gitlab.com/sprint-technology/watthelp/api/php:7.4-xdebug --build-arg xdebug=true
docker push registry.gitlab.com/sprint-technology/watthelp/api/php:7.4-xdebug
