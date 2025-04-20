import { useState, useCallback } from "react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { ImageIcon, Paperclip } from "lucide-react";

interface ImageUploaderProps {
  onImageSelect: (image: string) => void;
  className?: string;
}

export const ImageUploader = ({ onImageSelect, className }: ImageUploaderProps) => {
  const [isDragging, setIsDragging] = useState(false);

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      handleFile(file);
    }
  };

  const handleDrop = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(false);

    const file = e.dataTransfer.files?.[0];
    if (file) {
      handleFile(file);
    }
  }, [onImageSelect]);

  const handleDragOver = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(true);
  }, []);

  const handleDragLeave = useCallback((e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragging(false);
  }, []);

  const handlePaste = useCallback(async (e: ClipboardEvent) => {
    const items = e.clipboardData?.items;
    if (!items) return;

    for (let i = 0; i < items.length; i++) {
      const item = items[i];
      if (item.type.indexOf("image") === 0) {
        const blob = item.getAsFile();
        if (blob) {
          handleFile(blob);
        }
        break;
      }
    }
  }, [onImageSelect]);

  const handleFile = (file: Blob) => {
    const reader = new FileReader();
    reader.onloadend = () => {
      const base64String = reader.result as string;
      onImageSelect(base64String);
    };
    reader.readAsDataURL(file);
  };

  return (
    <Card className={`relative ${className || ''}`}>
      <div
        className={`relative flex flex-col items-center justify-center p-8 border-2 border-dashed rounded-md cursor-pointer transition-colors ${
          isDragging ? "border-primary" : "border-muted-foreground"
        }`}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        onPaste={handlePaste}
      >
        <ImageIcon className="h-12 w-12 text-muted-foreground mb-2" />
        <p className="text-sm text-muted-foreground mb-4">
          Drag and drop an image here, or click to select a file.
        </p>
        <input
          type="file"
          accept="image/*"
          className="absolute top-0 left-0 w-full h-full opacity-0 cursor-pointer"
          onChange={handleImageChange}
        />
        <Button variant="outline" size="sm">
          <Paperclip className="w-4 h-4 mr-2" />
          Select File
        </Button>
      </div>
    </Card>
  );
};
