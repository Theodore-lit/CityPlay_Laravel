import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { startLoading, stopLoading } from '@/stores/loader';

const attachLoadingInterceptors = (client) => {
    client.interceptors.request.use(
        (config) => {
            startLoading();
            return config;
        },
        (error) => {
            stopLoading();
            return Promise.reject(error);
        },
    );

    client.interceptors.response.use(
        (response) => {
            stopLoading();
            return response;
        },
        (error) => {
            stopLoading();
            return Promise.reject(error);
        },
    );
};

export function setupHttpInterceptors() {
    const client = axios.create({
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    attachLoadingInterceptors(client);
    window.axios = client;

    const nativeFetch = window.fetch.bind(window);
    window.fetch = async (...args) => {
        startLoading();
        try {
            return await nativeFetch(...args);
        } finally {
            stopLoading();
        }
    };

    router.on('start', () => startLoading());
    router.on('finish', () => stopLoading());
    router.on('error', () => stopLoading());
    router.on('cancel', () => stopLoading());

    return client;
}
