
import { showNotification } from "@/utils/notifications";

const API_KEY_STORAGE_KEY = 'kollect_it_api_key';

export const storeApiKey = (apiKey: string): void => {
  try {
    localStorage.setItem(API_KEY_STORAGE_KEY, apiKey);
    showNotification('API key saved successfully', 'success');
  } catch (error) {
    showNotification('Failed to save API key', 'error');
    console.error('Error storing API key:', error);
  }
};

export const getApiKey = (): string => {
  return localStorage.getItem(API_KEY_STORAGE_KEY) || '';
};

export const hasApiKey = (): boolean => {
  return !!localStorage.getItem(API_KEY_STORAGE_KEY);
};

export const clearApiKey = (): void => {
  localStorage.removeItem(API_KEY_STORAGE_KEY);
  showNotification('API key removed', 'info');
};
