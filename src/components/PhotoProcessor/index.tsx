
import { useState } from "react";
import { ImageUploader } from "@/components/ImageUploader";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { AIContentGenerator } from "@/components/AIContentGenerator";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";

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

  // Empty placeholder functions to satisfy the ImageUploaderProps interface
  const handleImageSelect = (imageData: string) => {
    // This function is needed for the interface but not used in this component
  };

  const handleAppraisalComplete = (result: string) => {
    // This function is needed for the interface but not used in this component
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
            onImageSelect={handleImageSelect}
            onAppraisalComplete={handleAppraisalComplete}
            onImagesProcessed={handleImagesProcessed}
            maxImages={15}
          />
        ) : activeTab === 'generate' ? (
          <AIContentGenerator images={processedImages} />
        ) : (
          <Card className="p-6 space-y-6">
            <div className="space-y-4">
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
                  <li>Export as a WooCommerce-ready CSV file (Simple or Advanced)</li>
                  <li>Upload to your WordPress media library and product listings</li>
                </ol>
              </div>
            </div>
            
            <div className="space-y-4">
              <h2 className="text-xl font-semibold">Recommended WordPress Plugins</h2>
              <p>Since you're using the Legacy Theme, these plugins will make your upload and management much easier:</p>
              
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Plugin</TableHead>
                      <TableHead>Purpose</TableHead>
                      <TableHead>Notes</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow>
                      <TableCell className="font-medium">WP All Import (with WooCommerce Add-On)</TableCell>
                      <TableCell>Bulk import products via CSV</TableCell>
                      <TableCell>Essential for your project</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell className="font-medium">Regenerate Thumbnails</TableCell>
                      <TableCell>Rebuild image sizes after upload</TableCell>
                      <TableCell>Keeps images optimized for Legacy</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell className="font-medium">Smush (Free or Pro)</TableCell>
                      <TableCell>Compress images automatically</TableCell>
                      <TableCell>Makes site faster without losing quality</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell className="font-medium">WooCommerce</TableCell>
                      <TableCell>Core store functionality</TableCell>
                      <TableCell>Already assumed, but confirm</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell className="font-medium">Product Import Export for WooCommerce</TableCell>
                      <TableCell>Another free CSV import/export tool</TableCell>
                      <TableCell>Simpler but less powerful than WP All Import</TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell className="font-medium">Yoast SEO</TableCell>
                      <TableCell>SEO for products and pages</TableCell>
                      <TableCell>Helps your products rank on Google</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
            
            <div className="space-y-4">
              <h2 className="text-xl font-semibold">Complete Workflow</h2>
              <div className="bg-muted p-4 rounded-md">
                <ol className="list-decimal pl-6 space-y-2">
                  <li>✅ Upload 15 photos</li>
                  <li>✅ Auto background removal + white background</li>
                  <li>✅ Resize + optimize for WordPress</li>
                  <li>✅ Generate AI product title, description, price</li>
                  <li>✅ Offer Simple or Advanced CSV</li>
                  <li>✅ Export and import easily into WordPress/WooCommerce</li>
                </ol>
              </div>
            </div>
            
            <div className="bg-muted p-4 rounded-md">
              <h3 className="font-medium">Note:</h3>
              <p className="text-sm">
                For background removal, you need an API key from <a href="https://www.remove.bg/" target="_blank" rel="noopener noreferrer" className="text-primary underline">Remove.bg</a>. 
                Without an API key, images will still be resized and optimized, but backgrounds won't be removed.
              </p>
              <p className="text-sm mt-2">
                For AI content generation, you need an OpenAI API key. The system uses GPT-4o-mini to analyze your product images and generate relevant content.
              </p>
            </div>
          </Card>
        )}
      </div>
    </div>
  );
};
