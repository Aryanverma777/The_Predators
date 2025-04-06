//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
// IMAGE UPLOAD AND DETECT DISEASE

import { InferenceClient } from "https://cdn.skypack.dev/@huggingface/inference";

const client = new InferenceClient("");

async function uploadImage() {
    const fileInput = document.getElementById("fileInput");
    const diseaseLabel = document.getElementById("diseaseLabel");
    const solutionLabel = document.getElementById("solutionLabel");

    if (!fileInput.files.length) {
        alert("Please select an image first!");
        return;
    }

    const file = fileInput.files[0];

    try {
        // 🔹 Step 1: Detect Disease
        const output = await client.imageClassification({
            data: file,
            model: "Diginsa/Plant-Disease-Detection-Project",
            provider: "hf-inference",
        });

        if (output.length === 0) {
            diseaseLabel.textContent = "No disease detected!";
            return;
        }

        // 🔹 Find the disease with the highest confidence score
        const bestPrediction = output.reduce((max, item) => (item.score > max.score ? item : max), output[0]);
        const diseaseName = bestPrediction.label;
        const confidence = (bestPrediction.score * 100).toFixed(2);

        diseaseLabel.innerHTML = `<strong>Disease:</strong> ${diseaseName} <br> <strong>Confidence:</strong> ${confidence}%`;

        // 🔹 Step 2: Get a solution using Chat Model
        solutionLabel.textContent = "Generating solution...";

        const chatResponse = await client.chatCompletion({
            provider: "hf-inference",
            model: "google/gemma-3-27b-it",
            messages: [
                {
                    role: "user",
                    content: [
                        {
                            type: "text",
                            text: `What is the best solution for treating ${diseaseName} in plants in short?`,
                        },
                    ],
                },
            ],
            max_tokens: 500,
        });

        if (chatResponse.choices && chatResponse.choices.length > 0) {
            const aiResponse = chatResponse.choices[0].message.content;
            solutionLabel.innerHTML = `<strong>Solution:</strong> ${aiResponse}`;
        } else {
            solutionLabel.textContent = "No response from AI!";
        }
    } catch (error) {
        console.error("Error:", error);
        diseaseLabel.textContent = "Error processing the image!";
        solutionLabel.textContent = "";
    }
}

document.getElementById("uploadButton").addEventListener("click", uploadImage);



// Groq cloud api key




//chatbot 

const API_KEY = "";
const API_URL = "https://api.groq.com/openai/v1/chat/completions";

const chatDisplay = document.getElementById("chat-display");
const userInput = document.getElementById("user-input");
const sendButton = document.getElementById("send-button");

async function fetchGroqData(messages) {
  try {
    const response = await fetch(API_URL, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${API_KEY}`,
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        model: "mixtral-8x7b-32768",
        messages: messages
      })
    });

    if (!response.ok) {
      const errorBody = await response.text();
      throw new Error(
        `HTTP error! status: ${response.status}, body: ${errorBody}`
      );
    }

    const data = await response.json();
    return data.choices[0].message.content;
  } catch (error) {
    console.error("Error fetching data:", error);
    throw new Error("Sorry, I encountered an error. Please try again later.");
  }
}

function appendMessage(content, isUser = false, isError = false) {
  const messageElement = document.createElement("div");
  messageElement.classList.add("message");
  messageElement.classList.add(
    isUser ? "user-message" : isError ? "error-message" : "bot-message"
  );
  messageElement.textContent = content;
  chatDisplay.appendChild(messageElement);
  chatDisplay.scrollTop = chatDisplay.scrollHeight;
}

async function handleUserInput() {
  const userMessage = userInput.value.trim();
  if (userMessage) {
    appendMessage(userMessage, true);
    userInput.value = "";

    try {
      const messages = [
        { role: "system", content: "You are a helpful assistant." },
        { role: "user", content: userMessage }
      ];

      const botResponse = await fetchGroqData(messages);
      appendMessage(botResponse);
    } catch (error) {
      appendMessage(error.message, false, true);
    }
  }
}

sendButton.addEventListener("click", handleUserInput);
userInput.addEventListener("keypress", (event) => {
  if (event.key === "Enter") {
    handleUserInput();
  }
});

chatDisplay.innerHTML = "";

appendMessage("Hello! How can I help you today?");
