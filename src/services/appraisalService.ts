import axios from 'axios';
import { promptTemplates } from '@/data/promptTemplates';

export interface AppraisalResult {
  appraisalText: string;
  error?: string;
  metadata?: {
    model: string;
    promptTokens?: number;
    completionTokens?: number;
    totalTokens?: number;
    timestamp?: string;
    templateId?: string;
  }
}

export const generateAppraisal = async (
  imageData: string,
  apiKey: string,
  templateId: string = 'standard'
): Promise<AppraisalResult> => {
  try {
    // Process the image data - ensure it's in the right format
    const base64Image = imageData.includes('base64,')
      ? imageData.split('base64,')[1]
      : imageData;
    
    // Get the appropriate prompt template
    const template = promptTemplates.find(t => t.id === templateId) || promptTemplates[0];
    
    // Set up model - default to gpt-4o-mini for cost efficiency
    const model = 'gpt-4o-mini';
    
    // Set up the request with a timeout
    const response = await axios.post(
      'https://api.openai.com/v1/chat/completions',
      {
        model: model,
        messages: [
          {
            role: 'user',
            content: [
              {
                type: 'text',
                text: template.text
              },
              {
                type: 'image_url',
                image_url: {
                  url: `data:image/jpeg;base64,${base64Image}`
                }
              }
            ]
          }
        ],
        max_tokens: 4000
      },
      {
        headers: {
          'Authorization': `Bearer ${apiKey}`,
          'Content-Type': 'application/json'
        },
        timeout: 60000 // 60-second timeout
      }
    );
    
    // Return the response with enhanced metadata
    return {
      appraisalText: response.data.choices[0].message.content,
      metadata: {
        model: model,
        promptTokens: response.data.usage?.prompt_tokens,
        completionTokens: response.data.usage?.completion_tokens,
        totalTokens: response.data.usage?.total_tokens,
        timestamp: new Date().toISOString(),
        templateId: templateId
      }
    };
  } catch (error: any) {
    console.error('OpenAI API error:', error);
    
    // Provide more specific error messages based on the error type
    if (axios.isAxiosError(error)) {
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        const statusCode = error.response.status;
        const errorData = error.response.data;
        
        if (statusCode === 401) {
          return {
            appraisalText: '',
            error: 'Invalid API key. Please check your OpenAI API key and try again.'
          };
        } else if (statusCode === 429) {
          return {
            appraisalText: '',
            error: 'OpenAI rate limit exceeded. Please try again later.'
          };
        } else if (statusCode === 400) {
          return {
            appraisalText: '',
            error: `Invalid request: ${errorData.error?.message || 'Unknown error'}`
          };
        } else {
          return {
            appraisalText: '',
            error: `API error (${statusCode}): ${errorData.error?.message || 'Unknown error'}`
          };
        }
      } else if (error.request) {
        // The request was made but no response was received
        return {
          appraisalText: '',
          error: 'No response from OpenAI. Please check your internet connection and try again.'
        };
      } else {
        // Something happened in setting up the request
        return {
          appraisalText: '',
          error: `Error setting up request: ${error.message}`
        };
      }
    }
    
    // For non-Axios errors
    return {
      appraisalText: '',
      error: error instanceof Error ? error.message : 'Unknown error occurred'
    };
  }
};
