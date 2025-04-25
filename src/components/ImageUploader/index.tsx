
import { useState, useCallback } from "react";
import { ImageIcon, Upload, X, Download, Image as ImageLucide } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Progress } from "@/components/ui/progress";
import { ImagePreview } from "@/components/ImagePreview";
import { showNotification } from "@/utils/notifications";
import { getImageMetadata, optimizeImage, removeBackground, dataURLtoBlob } from "@/utils/imageProcessing";
import JSZip from "jszip";
import { saveAs } from "file-saver";

export interface ImageUploaderProps {
  onImageSelect?: (imageData: string) => void;
  onAppraisalComplete?: (result: string) => void;
  maxImages?: number;
  autoProcess?: boolean;
}

interface UploadedImage {
  id: string;
  data: string;
  file: File;
  name: string;
  status: 'uploading' | 'processing' | 'done' | 'error';
  progress: number;
  processed?: string;
}

export const ImageUploader = ({ 
  onImageSelect, 
  onAppraisalComplete, 
  maxImages = 15, 
  autoProcess = false 
}: ImageUploaderProps) => {
  const [isDragging, setIsDragging] = useState(false);
  const [images, setImages] = useState<UploadedImage[]>([]);
  const [isProcessing, setIsProcessing] = useState(false);
  const [apiKey, setApiKey] = useState<string>('');

  const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(true);
  };

  const handleDragLeave = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(false);
  };

  const processImage = async (image: UploadedImage) => {
    try {
      // Update status to processing
      setImages(prev => prev.map(img => 
        img.id === image.id ? { ...img, status: 'processing', progress: 30 } : img
      ));

      // Step 1: Optimize the image
      const optimized = await optimizeImage(image.data, 1200);
      
      setImages(prev => prev.map(img => 
        img.id === image.id ? { ...img, progress: 60 } : img
      ));

      // Step 2: Remove background (this would integrate with Remove.bg or PhotoRoom API)
      // For now, we'll skip the actual API call and just use the optimized image
      const processed = optimized; // This would be replaced with the API response
      
      // Update the image with processed data
      setImages(prev => prev.map(img => 
        img.id === image.id ? { 
          ...img, 
          status: 'done', 
          progress: 100, 
          processed
        } : img
      ));

      return processed;
    } catch (error) {
      console.error('Error processing image:', error);
      setImages(prev => prev.map(img => 
        img.id === image.id ? { ...img, status: 'error', progress: 0 } : img
      ));
      showNotification(`Error processing image: ${(error as Error).message}`, "error");
      return null;
    }
  };

  const processAllImages = async () => {
    if (isProcessing) return;
    setIsProcessing(true);
    
    try {
      const results = await Promise.all(
        images.filter(img => img.status !== 'done').map(processImage)
      );
      
      if (results.every(Boolean)) {
        showNotification("All images processed successfully!", "success");
      }
    } catch (error) {
      console.error('Error processing images:', error);
      showNotification(`Error processing images: ${(error as Error).message}`, "error");
    } finally {
      setIsProcessing(false);
    }
  };

  const handleImageFiles = useCallback(async (files: File[]) => {
    if (!files.length) return;
    
    // Filter out non-image files
    const imageFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
    
    // Check if we'd exceed the max image count
    if (images.length + imageFiles.length > maxImages) {
      showNotification(`You can only upload up to ${maxImages} images.`, "error");
      return;
    }

    // Process each file
    const newImages: UploadedImage[] = [];
    
    for (const file of imageFiles) {
      const reader = new FileReader();
      
      // Use a Promise to handle the FileReader async behavior
      const imageData = await new Promise<string>((resolve) => {
        reader.onload = () => resolve(reader.result as string);
        reader.readAsDataURL(file);
      });
      
      newImages.push({
        id: crypto.randomUUID(),
        data: imageData,
        file,
        name: file.name,
        status: 'uploading',
        progress: 0
      });
    }
    
    // Add the new images
    setImages(prev => [...prev, ...newImages]);
    
    // Update progress for all new images
    setTimeout(() => {
      setImages(prev => 
        prev.map(img => 
          newImages.find(newImg => newImg.id === img.id) 
            ? { ...img, status: 'uploading', progress: 100 } 
            : img
        )
      );
      
      // If autoProcess is true, process the images automatically
      if (autoProcess) {
        processAllImages();
      }
    }, 500);

    // If there's only one image and onImageSelect is provided, call it
    if (imageFiles.length === 1 && onImageSelect) {
      onImageSelect(newImages[0].data);
    }
    
    showNotification(`${imageFiles.length} image${imageFiles.length !== 1 ? 's' : ''} uploaded.`, "success");
  }, [images, maxImages, autoProcess, onImageSelect]);

  const handleDrop = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(false);

    const files = Array.from(e.dataTransfer.files);
    handleImageFiles(files);
  }, [handleImageFiles]);

  const handleFileSelect = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files?.length) return;
    const files = Array.from(e.target.files);
    handleImageFiles(files);
    // Reset the input so the same file can be selected again
    e.target.value = '';
  }, [handleImageFiles]);

  const removeImage = useCallback((id: string) => {
    setImages(prev => prev.filter(img => img.id !== id));
  }, []);

  const downloadProcessedImages = useCallback(async () => {
    const processedImages = images.filter(img => img.status === 'done' && img.processed);
    if (!processedImages.length) {
      showNotification("No processed images to download.", "error");
      return;
    }

    try {
      const zip = new JSZip();
      
      // Add each processed image to the zip
      processedImages.forEach((img, index) => {
        // Convert data URL to blob
        const imageData = img.processed!.split(',')[1];
        const mimeType = img.processed!.split(',')[0].split(':')[1].split(';')[0];
        const extension = mimeType === 'image/webp' ? 'webp' : 'jpg';
        
        // Create formatted filename (product-image-01.webp)
        const paddedIndex = String(index + 1).padStart(2, '0');
        const fileName = `product-image-${paddedIndex}.${extension}`;
        
        // Add to zip
        zip.file(fileName, imageData, { base64: true });
      });
      
      // Generate and download the zip file
      const content = await zip.generateAsync({ type: "blob" });
      saveAs(content, "processed-images.zip");
      
      showNotification("Images downloaded successfully!", "success");
    } catch (error) {
      console.error('Error downloading images:', error);
      showNotification(`Error downloading images: ${(error as Error).message}`, "error");
    }
  }, [images]);

  return (
    <div className="space-y-6">
      {/* API Key Input */}
      <div className="flex flex-col gap-2">
        <label htmlFor="api-key" className="font-medium text-sm">
          Remove.bg API Key (optional)
        </label>
        <input
          type="text"
          id="api-key"
          placeholder="Enter your Remove.bg API key"
          className="border rounded-md px-3 py-2"
          value={apiKey}
          onChange={(e) => setApiKey(e.target.value)}
        />
        <p className="text-xs text-muted-foreground">
          Without an API key, images will be optimized but backgrounds won't be removed
        </p>
      </div>
      
      {/* Upload Zone */}
      <Card
        className={`p-6 border-2 border-dashed transition-colors ${
          isDragging ? 'border-primary bg-primary/10' : 'border-muted'
        }`}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        onDrop={handleDrop}
      >
        <div className="flex flex-col items-center justify-center gap-4">
          <ImageIcon className="h-12 w-12 text-muted-foreground" />
          <div className="text-center font-playfair">
            <p className="text-sm text-muted-foreground mb-2">
              Drag and drop images here, or click to select
            </p>
            <p className="text-xs text-muted-foreground">
              Upload up to {maxImages} images at once (.jpg, .png, .webp)
            </p>
            <p className="text-xs text-muted-foreground mt-1">
              {images.length}/{maxImages} images uploaded
            </p>
          </div>
          <Button variant="secondary" asChild>
            <label>
              <input
                type="file"
                multiple
                accept="image/*"
                className="hidden"
                onChange={handleFileSelect}
              />
              <Upload className="h-4 w-4 mr-2" />
              Select Images
            </label>
          </Button>
        </div>
      </Card>
      
      {/* Image Preview Grid */}
      {images.length > 0 && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-lg font-semibold">Uploaded Images</h3>
            <div className="flex gap-2">
              <Button 
                variant="outline" 
                onClick={() => setImages([])}
                disabled={isProcessing}
              >
                <X className="h-4 w-4 mr-2" />
                Clear All
              </Button>
              <Button 
                variant="default" 
                onClick={processAllImages}
                disabled={isProcessing || !images.some(img => img.status !== 'done')}
              >
                <ImageLucide className="h-4 w-4 mr-2" />
                Process Images
              </Button>
              <Button
                variant="secondary"
                onClick={downloadProcessedImages}
                disabled={!images.some(img => img.status === 'done')}
              >
                <Download className="h-4 w-4 mr-2" />
                Download All
              </Button>
            </div>
          </div>
          
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            {images.map((image) => (
              <div key={image.id} className="relative">
                <div className="absolute top-2 right-2 z-10 flex gap-2">
                  <Button
                    size="icon"
                    variant="destructive"
                    className="w-6 h-6"
                    onClick={() => removeImage(image.id)}
                    disabled={isProcessing && image.status === 'processing'}
                  >
                    <X className="h-3 w-3" />
                  </Button>
                </div>
                
                {/* Show either the original or processed image */}
                <div className="aspect-square relative rounded-md overflow-hidden border border-border">
                  <img
                    src={image.status === 'done' && image.processed ? image.processed : image.data}
                    alt={`Image ${image.name}`}
                    className="w-full h-full object-contain bg-white"
                  />
                  
                  {/* Status overlay */}
                  {image.status === 'uploading' && image.progress < 100 && (
                    <div className="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                      <div className="w-4/5">
                        <Progress value={image.progress} className="h-2" />
                      </div>
                    </div>
                  )}
                  {image.status === 'processing' && (
                    <div className="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                      <div className="text-white text-center">
                        <p className="text-sm font-medium mb-2">Processing...</p>
                        <Progress value={image.progress} className="h-2 w-36" />
                      </div>
                    </div>
                  )}
                  {image.status === 'error' && (
                    <div className="absolute inset-0 flex items-center justify-center bg-red-500 bg-opacity-50">
                      <div className="text-white text-center">
                        <p className="text-sm font-medium">Error</p>
                      </div>
                    </div>
                  )}
                </div>
                
                {/* Image name and status */}
                <div className="mt-2">
                  <p className="text-sm font-medium truncate">{image.name}</p>
                  <p className="text-xs text-muted-foreground capitalize">
                    Status: {image.status}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};
