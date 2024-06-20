FROM node:20.10.0-alpine
ENV APP_ROOT /web/susan_admin
WORKDIR $APP_ROOT

COPY package.json $APP_ROOT
COPY yarn.lock $APP_ROOT

RUN yarn install

COPY . $APP_ROOT

EXPOSE 3001
CMD ["yarn", "dev"]
