UID:=`id -u`
GID:=`id -g`

run:
	docker-compose up --build

logs:
	DOCKER_UID=$(UID):$(GID) docker-compose logs --tail=1000 -f $(filter-out $@,$(MAKECMDGOALS))

test:
	DOCKER_UID=$(UID):$(GID) docker-compose run --rm php php ./vendor/bin/simple-phpunit

stop:
	DOCKER_UID=$(UID):$(GID) docker stop `docker ps -aq`

clean: stop
	DOCKER_UID=$(UID):$(GID) docker rm `docker ps -aq`

update-registry:
	docker/build_image.sh

