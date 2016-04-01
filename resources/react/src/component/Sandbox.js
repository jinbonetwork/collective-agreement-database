import React, { Component } from 'react';
import axios from 'axios';

export default class Articles extends Component {
  constructor() {
    super();

    this.state = {
      // view infos
      chapters: [],
      chapterArticles: {},
      articles: [],
      // view states infos
      articleChecked: {},
    };
  }

  render() {
    const chapters = this.state.chapters.map((item, index) => {
      const id = `chapter-${item.value}`;
      return (
        <li key={index} className="box"><div className="radio-button">
          <input type="radio" name="chapter" id={id} value={item.value}
            onClick={this.handleChapterClick.bind(this)}
          />
          <label htmlFor={id}>{item.name}</label>
        </div></li>
      );
    });

    const articles = this.state.articles.map((item, index) => {
      const id = `article-${item.value}`;
      return (
        <li key={index} className="checkbox-wrap">
          <input type="checkbox" name="article" id={id} value={item.value}
            onClick={this.handleArticleClick.bind(this)}
          />
          <label className="checkbox" htmlFor={id}>
            <i className="unchecked fa fa-square-o"></i>
            <i className="checked fa fa-check-square"></i>
          </label>
          {' '}
          <label className="label" htmlFor={id}>{item.name}</label>
        </li>
      );
    });

    return (
      <div className="sub-categories">
        <div className="example">
          <div className="chapters">
            <ul className="shadow">
              {chapters}
            </ul>
          </div>
          <div className="articles">
            <ul className="shadow">
              {articles}
            </ul>
          </div>
        </div>
      </div>
    );
  }

  componentDidMount() {
    const url = '/api';

    axios.get(url)
    .then(({ data }) => {
      this.parseArticles(data[0].items[0]);

      traverseItem(data[1]);
    });
  }

  parseArticles(sopt) {
    const chapters = [];
    const chapterArticles = {};

    sopt.options.forEach((option) => {
      if (option.depth === 0) {
        chapters.push(option);
      } else {
        const chapter = option.parent;
        if (!chapterArticles[chapter]) {
          chapterArticles[chapter] = [];
        }
        chapterArticles[chapter].push(option);
      }
    });

    this.setState({
      chapters,
      chapterArticles,
    });
  }

  handleChapterClick(e) {
    const value = e.target.value;
    this.setState({
      articles: this.state.chapterArticles[value] || []
    });
  }

  handleArticleClick(e) {
    const value = e.target.value;
    const checked = e.target.checked;
  }
}

function traverseItem(item) {
  if (item.items) {
    item.items.forEach(traverseItem);
  } else if (item.options) {
    item.options.forEach((option) => {
    });
  }
}
