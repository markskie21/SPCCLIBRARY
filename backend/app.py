from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import os

app = Flask(__name__)
CORS(app)

# Load the Excel file
def load_books():
    try:
        # Adjust the path to your Excel file
        df = pd.read_excel('books.xlsx')
        return df.to_dict('records')
    except Exception as e:
        print(f"Error loading books: {e}")
        return []

# Store books in memory
books = load_books()

@app.route('/api/books/search', methods=['POST'])
def search_books():
    data = request.json
    search_term = data.get('search', '').lower()
    category = data.get('category', '')
    author = data.get('author', '')
    sort_by = data.get('sortBy', 'title')
    
    # Filter books based on search criteria
    filtered_books = books
    
    if search_term:
        filtered_books = [
            book for book in filtered_books
            if search_term in book.get('title', '').lower() or
               search_term in book.get('author', '').lower() or
               search_term in book.get('isbn', '').lower()
        ]
    
    if category:
        filtered_books = [
            book for book in filtered_books
            if book.get('category', '').lower() == category.lower()
        ]
    
    if author:
        filtered_books = [
            book for book in filtered_books
            if book.get('author', '').lower() == author.lower()
        ]
    
    # Sort books
    if sort_by:
        filtered_books.sort(key=lambda x: x.get(sort_by, ''))
    
    return jsonify(filtered_books)

@app.route('/api/books/borrow', methods=['POST'])
def borrow_book():
    data = request.json
    book_id = data.get('bookId')
    user_id = data.get('userId')
    
    # Here you would implement the borrowing logic
    # For example, update the book's availability in the Excel file
    
    return jsonify({'message': 'Book borrowed successfully'})

@app.route('/api/books/categories', methods=['GET'])
def get_categories():
    categories = list(set(book.get('category', '') for book in books))
    return jsonify(categories)

@app.route('/api/books/authors', methods=['GET'])
def get_authors():
    authors = list(set(book.get('author', '') for book in books))
    return jsonify(authors)

if __name__ == '__main__':
    app.run(debug=True) 