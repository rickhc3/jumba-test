FROM node:14-alpine

RUN apk add --no-cache python3 make g++
# RUN apk add --update python3 make g++ && rm -rf /var/cache/apk/*

WORKDIR /app
COPY package.json ./
RUN npm install
COPY . .
ENV NUXT_HOST=0.0.0.0
ENV NUXT_PORT=7000
CMD npm run dev
EXPOSE 7000
