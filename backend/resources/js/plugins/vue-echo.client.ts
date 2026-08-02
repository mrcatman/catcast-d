import Echo from 'laravel-echo';

import { WEBSOCKETS_HOST } from "@/constants/urls";

import io from 'socket.io-client';
window.io = io;

export default defineNuxtPlugin(() => {
  const echo = new Echo({
    broadcaster: 'socket.io',
    host: WEBSOCKETS_HOST
  });

  return {
    provide: {
      echo
    }
  }
})


