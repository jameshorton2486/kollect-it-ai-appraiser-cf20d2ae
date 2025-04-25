
interface ImageMetadata {
  width: number;
  height: number;
  format: string;
  size: string;
}

export const getImageMetadata = (dataUrl: string): Promise<ImageMetadata> => {
  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => {
      // Extract format from data URL
      const format = dataUrl.split(';')[0].split('/')[1];
      // Calculate size in KB
      const sizeInKb = Math.round(dataUrl.length * 0.75 / 1024);
      
      resolve({
        width: img.width,
        height: img.height,
        format,
        size: `${sizeInKb} KB`
      });
    };
    img.src = dataUrl;
  });
};

export const optimizeImage = async (dataUrl: string, maxWidth = 1200): Promise<string> => {
  return new Promise((resolve, reject) => {
    try {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        let width = img.width;
        let height = img.height;
        
        // Calculate new dimensions while maintaining aspect ratio
        if (width > maxWidth) {
          height = Math.round((height * maxWidth) / width);
          width = maxWidth;
        }
        
        canvas.width = width;
        canvas.height = height;
        
        const ctx = canvas.getContext('2d');
        if (!ctx) return reject(new Error("Could not get canvas context"));
        
        // First fill with white background (for transparency)
        ctx.fillStyle = "#FFFFFF";
        ctx.fillRect(0, 0, width, height);
        
        // Then draw the image on top
        ctx.drawImage(img, 0, 0, width, height);
        
        // Convert to WebP if supported, otherwise JPEG
        const format = 'image/webp';
        const quality = 0.85;
        
        resolve(canvas.toDataURL(format, quality));
      };
      
      img.onerror = () => {
        reject(new Error("Failed to load image"));
      };
      
      img.src = dataUrl;
    } catch (error) {
      reject(error);
    }
  });
};

// Function to remove image background using RemoveBG API
export const removeBackground = async (imageData: string, apiKey: string): Promise<string> => {
  if (!apiKey) {
    throw new Error("API key is required for background removal");
  }
  
  // Convert base64 data to blob
  const base64Data = imageData.split(',')[1];
  const byteCharacters = atob(base64Data);
  const byteArrays = [];
  
  for (let offset = 0; offset < byteCharacters.length; offset += 1024) {
    const slice = byteCharacters.slice(offset, offset + 1024);
    const byteNumbers = new Array(slice.length);
    for (let i = 0; i < slice.length; i++) {
      byteNumbers[i] = slice.charCodeAt(i);
    }
    byteArrays.push(new Uint8Array(byteNumbers));
  }
  
  const blob = new Blob(byteArrays, { type: 'image/png' });
  
  // Create form data for API request
  const formData = new FormData();
  formData.append('image_file', blob);
  formData.append('size', 'auto');
  formData.append('bg_color', 'white');
  
  try {
    const response = await fetch('https://api.remove.bg/v1.0/removebg', {
      method: 'POST',
      headers: {
        'X-Api-Key': apiKey
      },
      body: formData
    });
    
    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.errors?.[0]?.title || 'Background removal failed');
    }
    
    const data = await response.blob();
    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onloadend = () => resolve(reader.result as string);
      reader.readAsDataURL(data);
    });
  } catch (error) {
    console.error('Error removing background:', error);
    throw error;
  }
};

// Helper function to convert dataURL to Blob
export const dataURLtoBlob = (dataURL: string): Blob => {
  const arr = dataURL.split(',');
  const mime = arr[0].match(/:(.*?);/)?.[1] || 'image/png';
  const bstr = atob(arr[1]);
  let n = bstr.length;
  const u8arr = new Uint8Array(n);
  
  while (n--) {
    u8arr[n] = bstr.charCodeAt(n);
  }
  
  return new Blob([u8arr], { type: mime });
};
