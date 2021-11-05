VERSION=latest
NAME=release-check

all: build push
build:
	docker build --no-cache=true -t $(NAME):$(VERSION) .
#	docker tag $(NAME):$(VERSION) docker.sunet.se/$(NAME):$(VERSION)
update:
	docker build -t $(NAME):$(VERSION) .
#	docker tag $(NAME):$(VERSION) docker.sunet.se/$(NAME):$(VERSION)
push:
#	docker push docker.sunet.se/$(NAME):$(VERSION)

run:
	#docker run -v /var/www:/var/www -v /etc/ssl:/etc/ssl -v /etc/dehydrated:/etc/dehydrated -v /etc/shibboleth:/etc/shibboleth -v /etc/passwd:/etc/passwd:ro -v /etc/group:/etc/group:ro $(NAME):$(VERSION) 
	docker run -v /var/www:/var/www -v /etc/ssl:/etc/ssl -v /etc/dehydrated:/etc/dehydrated -v /etc/shibboleth/certs:/etc/shibboleth/certs -p 443:443 -d $(NAME):$(VERSION) 




