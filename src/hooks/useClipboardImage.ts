
import { useState, useEffect, useCallback } from 'react';
import { showNotification } from '@/utils/notifications';

export const useClipboardImage = () => {
  const [image, setImage] = useState<string | null>(null);
  
  const handlePaste = useCallback(async (e: ClipboardEvent) => {
    try {
      const clipboardItems = e.clipboardData?.items;
      
      if (!clipboardItems) {
        showNotification("No items found in clipboard", "error");
        return;
      }
      
      for (let i = 0; i < clipboardItems.length; i++) {
        if (clipboardItems[i].type.indexOf('image') !== -1) {
          const blob = clipboardItems[i].getAsFile();
          if (!blob) continue;
          
          const reader = new FileReader();
          reader.onload = (e) => {
            const result = e.target?.result as string;
            setImage(result);
            showNotification("Image pasted successfully", "success");
          };
          
          reader.onerror = () => {
            showNotification("Failed to read image data", "error");
          };
          
          reader.readAsDataURL(blob);
          return;
        }
      }
      
      showNotification("No image found in clipboard", "info");
    } catch (error) {
      showNotification("Error accessing clipboard", "error");
    }
  }, []);
  
  useEffect(() => {
    const pasteListener = (e: ClipboardEvent) => {
      handlePaste(e);
    };
    
    document.addEventListener('paste', pasteListener);
    
    return () => {
      document.removeEventListener('paste', pasteListener);
    };
  }, [handlePaste]);
  
  return { image, handlePaste };
};
