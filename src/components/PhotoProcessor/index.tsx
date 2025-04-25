
import { useState } from "react";
import { ImageUploader } from "@/components/ImageUploader";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { AIContentGenerator } from "@/components/AIContentGenerator";

export const PhotoProcessor = () => {
  const [activeTab, setActiveTab] = useState<'upload' | 'generate' | 'about'>('upload');
  const [processedImages, setProcessedImages] = useState<Array<{id: string, processed: string, name: string}>>([]);

  const handleImagesProcessed = (images: Array<{id: string, processed: string, name: string}>) => {
    setProcessedImages(images);
    if (images.length > 0) {
      // Automatically switch to generate tab when images are processed
      setActiveTab('generate');
    }
  };

  return (
    <div className="max-w-6xl mx-auto p-4 md:p-6 space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold font-playfair">Product Photo Processor</h1>
        <p className="text-muted-foreground mt-2">
          Upload, optimize and prepare your product photos for WordPress
        </p>
      </div>
      
      <div className="flex border-b">
        <button
          className={`py-3 px-4 ${activeTab === 'upload' ? 'border-b-2 border-primary font-medium' : 'text-muted-foreground'}`}
          onClick={() => setActiveTab('upload')}
        >
          Upload & Process
        </button>
        <button
          className={`py-3 px-4 ${activeTab === 'generate' ? 'border-b-2 border-primary font-medium' : 'text-muted-foreground'}`}
          onClick={() => setActiveTab('generate')}
          disabled={processedImages.length === 0}
        >
          Generate Content
        </button>
        <button
          className={`py-3 px-4 ${activeTab === 'about' ? 'border-b-2 border-primary font-medium' : 'text-muted-foreground'}`}
          onClick={() => setActiveTab('about')}
        >
          About
        </button>
      </div>
      
      <div className="mt-6">
        {activeTab === 'upload' ? (
          <ImageUploader 
            onImagesProcessed={handleImagesProcessed} 
          />
        ) : activeTab === 'generate' ? (
          <AIContentGenerator images={processedImages} />
        ) : (
          <Card className="p-6 space-y-4">
            <h2 className="text-xl font-semibold">How It Works</h2>
            
            <div className="space-y-2">
              <h3 className="font-medium">Product Photo Processor</h3>
              <p>This tool helps you prepare product images for your WordPress website using the Legacy theme by:</p>
              <ul className="list-disc pl-6 space-y-1">
                <li>Removing image backgrounds (with Remove.bg API)</li>
                <li>Resizing images to optimal dimensions (1200px width)</li>
                <li>Converting to web-friendly formats (WebP or JPEG)</li>
                <li>Optimizing file sizes for faster loading</li>
                <li>Batch downloading all processed images</li>
                <li>Generating AI product titles, descriptions, and pricing</li>
              </ul>
            </div>
            
            <div className="space-y-2">
              <h3 className="font-medium">How to use:</h3>
              <ol className="list-decimal pl-6 space-y-2">
                <li>Drag and drop up to 15 images or use the file picker</li>
                <li>Add your Remove.bg API key for background removal (optional)</li>
                <li>Click "Process Images" to optimize your photos</li>
                <li>Review the processed images</li>
                <li>Generate AI content based on your product images</li>
                <li>Edit content if needed and download everything</li>
                <li>Upload to your WordPress media library and product listings</li>
              </ol>
            </div>
            
            <div className="bg-muted p-4 rounded-md">
              <h3 className="font-medium">Note:</h3>
              <p className="text-sm">
                For background removal, you need an API key from <a href="https://www.remove.bg/" target="_blank" rel="noopener noreferrer" className="text-primary underline">Remove.bg</a>. 
                Without an API key, images will still be resized and optimized, but backgrounds won't be removed.
              </p>
              <p className="text-sm mt-2">
                For AI content generation, you need an OpenAI API key. The system uses GPT-4 to analyze your product images and generate relevant content.
              </p>
            </div>
          </Card>
        )}
      </div>
    </div>
  );
};
