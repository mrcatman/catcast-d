FROM node:16.0-alpine
WORKDIR /echo
COPY package*.json ./
COPY laravel-echo-server.json ./
RUN npm ci
CMD [ "npm", "run", "chat" ]