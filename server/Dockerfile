FROM node:14-alpine
RUN apk update

WORKDIR /server

COPY /server/app /server/app
COPY /server/bin /server/bin
COPY /server/static /server/static
COPY /server/uploads /server/uploads
COPY /server/index.ts /server/index.ts
COPY /server/package.json /server/package.json
COPY /server/package-lock.json /server/package-lock.json
COPY /server/tsconfig.json /server/tsconfig.json

RUN cd /server

RUN npm install
RUN npm rebuild argon2
RUN npm i typescript
RUN npm i ts-node

CMD [ "npm", "run", "dev"]
