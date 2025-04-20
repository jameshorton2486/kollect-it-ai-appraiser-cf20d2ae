
import React, { useEffect, useState } from 'react';
import { Card } from "@/components/ui/card";
import { ImageIcon, LoaderCircle } from "lucide-react";
import { getImageMetadata, optimizeImage } from "@/utils/imageProcessing";
import { Progress } from "@/components/ui/progress";

interface ImagePreviewProps {
  imageData: string | null;
  onOptimizedImage?: (optimizedData: string) => void;
}

interface ImageMetadata {
  width: number;
  height: number;
  format: string;
  size: string;
}

export const ImagePreview: React.FC<ImagePreviewProps> = ({ imageData, onOptimizedImage }) => {
  const [metadata, setMetadata] = useState<ImageMetadata | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    if (imageData) {
      setIsProcessing(true);
      setProgress(25);

      const processImage = async () => {
        // Get metadata
        const meta = await getImageMetadata(imageData);
        setMetadata(meta);
        setProgress(50);

        // Optimize image
        const optimized = await optimizeImage(imageData);
        setProgress(75);
        
        if (onOptimizedImage) {
          onOptimizedImage(optimized);
        }
        
        setProgress(100);
        setTimeout(() => setIsProcessing(false), 500);
      };

      processImage();
    } else {
      setMetadata(null);
      setIsProcessing(false);
      setProgress(0);
    }
  }, [imageData, onOptimizedImage]);

  if (!imageData) {
    return (
      <Card className="h-[400px] flex items-center justify-center bg-muted">
        <div className="text-center space-y-4">
          <ImageIcon className="w-12 h-12 mx-auto text-muted-foreground" />
          <p className="text-muted-foreground">
            Paste an image (Ctrl+V) to begin
          </p>
        </div>
      </Card>
    );
  }
  
  return (
    <Card className="overflow-hidden">
      <div className="relative">
        {isProcessing && (
          <div className="absolute inset-0 bg-background/80 backdrop-blur-sm flex flex-col items-center justify-center z-10">
            <LoaderCircle className="w-8 h-8 animate-spin text-primary mb-2" />
            <p className="text-sm text-muted-foreground">Processing image...</p>
            <div className="w-48 mt-2">
              <Progress value={progress} />
            </div>
          </div>
        )}
        <div className="max-h-[600px] overflow-auto text-center p-4">
          <img 
            src={imageData} 
            alt="Item to appraise" 
            className="max-w-full max-h-[600px] object-contain mx-auto"
          />
        </div>
        {metadata && (
          <div className="p-4 border-t bg-muted/50">
            <div className="grid grid-cols-2 gap-2 text-sm">
              <div>Dimensions: {metadata.width} x {metadata.height}px</div>
              <div>Format: {metadata.format.toUpperCase()}</div>
              <div>Size: {metadata.size}</div>
            </div>
          </div>
        )}
      </div>
    </Card>
  );
};
