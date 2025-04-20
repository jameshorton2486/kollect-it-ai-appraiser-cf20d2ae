
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { ImageIcon, XCircle } from "lucide-react";

interface ImagePreviewProps {
  imageData: string;
  onRemove?: () => void;
  className?: string;
}

export const ImagePreview = ({ imageData, onRemove, className }: ImagePreviewProps) => {
  return (
    <Card className={`relative overflow-hidden ${className}`}>
      {onRemove && (
        <Button
          variant="destructive"
          size="icon"
          className="absolute top-2 right-2 z-10"
          onClick={onRemove}
        >
          <XCircle className="h-4 w-4" />
        </Button>
      )}
      <div className="aspect-video relative">
        {imageData ? (
          <img
            src={imageData}
            alt="Item preview"
            className="w-full h-full object-contain"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center bg-muted">
            <ImageIcon className="h-12 w-12 text-muted-foreground" />
          </div>
        )}
      </div>
    </Card>
  );
};
