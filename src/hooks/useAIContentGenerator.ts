
import { useState } from "react";
import { showNotification } from "@/utils/notifications";

interface ContentContext {
  brand?: string;
  material?: string;
  period?: string;
  notes?: string;
}

interface GeneratedContent {
  title: string;
  description: string;
  priceRange: string;
}

export function useAIContentGenerator() {
  const [isLoading, setIsLoading] = useState(false);

  const generateContent = async (
    imageUrl: string, 
    apiKey: string, 
    context: ContentContext = {}
  ): Promise<GeneratedContent> => {
    setIsLoading(true);
    
    try {
      // Get only the base64 data part of the URL
      const base64Data = imageUrl.split(',')[1];
      
      // Create prompt with context information
      let contextPrompt = "";
      if (context.brand) contextPrompt += `Brand: ${context.brand}\n`;
      if (context.material) contextPrompt += `Material: ${context.material}\n`;
      if (context.period) contextPrompt += `Period/Era: ${context.period}\n`;
      if (context.notes) contextPrompt += `Additional Notes: ${context.notes}\n`;
      
      // Create the GPT-4 API request
      const response = await fetch("https://api.openai.com/v1/chat/completions", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${apiKey}`
        },
        body: JSON.stringify({
          model: "gpt-4o-mini", // Using GPT-4o-mini which supports images
          messages: [
            {
              role: "system",
              content: `You are an expert on antiques, collectibles, and product photography. 
              Analyze the product image and generate three pieces of information:
              
              1. A concise, appealing Product Title under 60 characters
              2. A detailed Product Description (150-200 words) that includes material, style, dimensions (if visible), 
                 approximate age/era, suggested uses, and any notable features or historical significance
              3. A fair market Value Range estimate (e.g., "$125–$175")
              
              Format your response in JSON with the keys: "title", "description", and "priceRange".
              Be accurate and realistic in your descriptions. Use a professional, editorial tone.
              If you can't determine certain aspects from the image, focus on what you can see. 
              Don't include dimensions unless they're clearly evident or provided in the context.
              Your description should be detailed but factual - avoid marketing language or hyperbole.`
            },
            {
              role: "user",
              content: [
                {
                  type: "text",
                  text: contextPrompt || "Please analyze this product image and generate content."
                },
                {
                  type: "image_url",
                  image_url: {
                    url: `data:image/jpeg;base64,${base64Data}`
                  }
                }
              ]
            }
          ],
          temperature: 0.7
        })
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error?.message || "API request failed");
      }

      const data = await response.json();
      const content = data.choices[0]?.message?.content;
      
      if (!content) {
        throw new Error("No content returned from API");
      }

      // Parse the JSON response
      try {
        // Handle potential JSON within markdown code blocks
        let jsonStr = content;
        
        // If the response is wrapped in markdown code blocks, extract the JSON
        const jsonMatch = content.match(/```(?:json)?\s*([\s\S]*?)\s*```/);
        if (jsonMatch && jsonMatch[1]) {
          jsonStr = jsonMatch[1];
        }
        
        const result = JSON.parse(jsonStr);
        
        return {
          title: result.title || "Untitled Product",
          description: result.description || "No description available.",
          priceRange: result.priceRange || "Value unknown"
        };
      } catch (parseError) {
        console.error("Failed to parse AI response:", parseError);
        console.log("Raw content:", content);
        
        // Fallback: try to extract information manually using regex
        const titleMatch = content.match(/Title:\s*([^\n]+)/i);
        const descriptionMatch = content.match(/Description:\s*([^#]+)/is);
        const priceMatch = content.match(/Value Range:\s*([^\n]+)/i) || content.match(/Price Range:\s*([^\n]+)/i);
        
        return {
          title: titleMatch?.[1]?.trim() || "Untitled Product",
          description: descriptionMatch?.[1]?.trim() || "No description available.",
          priceRange: priceMatch?.[1]?.trim() || "Value unknown"
        };
      }
      
    } catch (error) {
      console.error("Error generating content:", error);
      throw error;
    } finally {
      setIsLoading(false);
    }
  };

  return {
    generateContent,
    isLoading
  };
}
