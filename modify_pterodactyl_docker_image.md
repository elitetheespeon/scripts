# How to modify a Pterodactyl Docker image
This is useful for modifying an existing image to add support for something (such as git or a library), it assumes you are modifying an existing image and pushing your own modified version up to quay.

### Login to repo with docker
`docker login quay.io`

### Pull latest image to modify
`docker pull quay.io/pterodactyl/core:source`

### List docker images to get "IMAGE ID"
`docker images`

```
This will list all docker images and their ID. Note the image ID of the docker image you want to modify.
```

### Run docker image ID "5b9a05334dfd" as a container with root access using bash (some use ash or sh)
`docker run --user=root -it 5b9a05334dfd /bin/bash`

```
Now you have a root shell in the container to do whatever modifications needed (apt install package etc)
Exit and kill the container with "exit" to get back to your own shell
```

### List all docker containers to get "CONTAINER ID", the one just exited should be at the top
`docker ps -a`

### Commit docker container with ID and name it as our repo path + tag
`docker commit 061d310af2ed quay.io/somedev/images:core_source_git`

### Commit change to fix up entrypoint as we forcibly changed it to be bash, and it needs to be what ptero expects
`docker commit --change='CMD ["/bin/bash", "/entrypoint.sh"]' 061d310af2ed quay.io/somedev/images:core_source_git`

### Push image to repo
`docker push quay.io/somedev/images:core_source_git`
