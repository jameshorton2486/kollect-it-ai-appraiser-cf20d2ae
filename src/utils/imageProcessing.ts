
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
  return new Promise((resolve) => {
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
      if (!ctx) return resolve(dataUrl);
      
      ctx.drawImage(img, 0, 0, width, height);
      resolve(canvas.toDataURL('image/jpeg', 0.85));
    };
    img.src = dataUrl;
  });
};
