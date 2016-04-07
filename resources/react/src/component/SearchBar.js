import React, { Component } from 'react';
import axios from 'axios';

import { storeLabels, inQuery, toggleInQuery, changeInQuery } from '../util/utils';

import QueryLabelList from './SearchBar/QueryLabelList';
import OrgCategory from './SearchBar/OrgCategory';
import ArticleCategory from './SearchBar/ArticleCategory';

export default class SearchBar extends Component {
  constructor() {
    super();

    this.state = {
      // search bar view infos
      labels: [],
      chapters: [],
      chapterArticles: {},
      orgCategories: [],
      // search bar state
      query: {},
      articles: [],
    };
  }

  render() {
    return (
      <form
        onSubmit={this.handleSubmit.bind(this)}
      >
        <div className="search-keyword-container">

          <div className="general-keyword box shadow">
            <div className="header"><i className="fa fa-search"></i></div>
            <QueryLabelList
              query={this.state.query} labels={this.state.labels}
              onClick={this.handleClickQueryLabel.bind(this)}
            />
            <div className="keyword-input">
              <input type="text" name="keyword" placeholder="검색어를 입력하세요."
                ref={(ref) => { this.input = ref; }}
              />
            </div>
          </div>

          <div className="categorized-keyword">
            <div className="search-button box">
              <button type="submit" name="search">검색</button>
            </div>

            <div className="categories box">
              <div className="selection"><div className="radio-button">
                <input type="radio" id="cat-example"
                  name="category" value="example"
                  onClick={this.handleCategoryClick.bind(this)}
                />
                <label htmlFor="cat-example">
                  2015 모범 단체협약안<i className="fa fa-angle-down"></i>
                </label>
              </div></div>
              <div className="selection"><div className="radio-button">
                <input type="radio" id="cat-organ" name="category" value="organ"
                  onClick={this.handleCategoryClick.bind(this)}
                />
                <label htmlFor="cat-organ">
                  조직 현황별<i className="fa fa-angle-down"></i>
                </label>
              </div></div>
            </div>

            <div className="sub-categories">
              <ArticleCategory
                query={this.state.query}
                chapters={this.state.chapters}
                articles={this.state.articles}
                onChapterClick={this.handleChapterClick.bind(this)}
                onCheckboxClick={this.handleCheckboxClick.bind(this)}
              />
              <OrgCategory
                query={this.state.query}
                catetories={this.state.orgCategories}
                onCheckboxClick={this.handleCheckboxClick.bind(this)}
                onSelectSelect={this.handleSelectSelect.bind(this)}
				onSelectClick={this.handleSelectClick.bind(this)}
              />
            </div>
          </div>
        </div>
      </form>
    );
  }

  componentDidMount() {
    const queryStr = window.location.search;

    // make search bar category options
    const url = '/api';
    axios.get(url)
    .then(({ data }) => {
      // store label for query option label
      let labels;
	  let self = this;
	  let orgItems = [];
      labels = storeLabels(this.state.labels, data[0].items[0]);
	  data[1].items[0].items.forEach((item) => {
	    labels = storeLabels(self.state.labels, item);
		orgItems.push(item);
      });
      labels = storeLabels(this.state.labels, data[1].items[1]);
	  orgItems.push(data[1].items[1]);
      labels = storeLabels(this.state.labels, data[1].items[2]);
	  orgItems.push(data[1].items[2]);
      labels = storeLabels(this.state.labels, data[1].items[3]);
	  orgItems.push(data[1].items[3]);
      labels = storeLabels(this.state.labels, data[1].items[4]);
	  orgItems.push(data[1].items[4]);
      labels = storeLabels(this.state.labels, data[1].items[5]);
	  orgItems.push(data[1].items[5]);

      const { chapters, chapterArticles } = parseArticles(data[0].items[0]);

      this.setState({
        labels,
        chapters,
        chapterArticles,
        orgCategories: orgItems
      });

      // reset query state from query string
      const queries = this.props.location.query;
      const query = {};
      for (const field in queries) {
        if (field === 'q') {
          this.input.value = queries.q || '';
		} else if(field === 'page') {
		  continue;
        } else {
          const valuesAsInt = JSON.parse(queries[field]);
          // value should be string in query
          const values = valuesAsInt.map((value) => `${value}`);
          query[field] = values;
        }
      }
      this.setState({ query });
    });
  }

  handleClickQueryLabel(field, value) {
    this.setState({
      query: toggleInQuery(this.state.query, field, value)
    });
  }

  handleCheckboxClick(field, value) {
    this.setState({
      query: toggleInQuery(this.state.query, field, value)
    });
  }

  handleSelectSelect(field, value) {
	this.setState({
      query: changeInQuery(this.state.query, field, value)
	});
  }

  handleChapterClick(field, value, nsubs) {
	if(parseInt(nsubs)) {
      this.setState({
        articles: this.state.chapterArticles[value] || []
      });
	} else {
	  this.setState({
	    articles: [],
        query: toggleInQuery(this.state.query, field, value)
      });
	}
  }

  handleSelectClick(value) {
    window.$('ul#'+value).slideToggle();
  }

  handleCategoryClick(e) {
    window.$('.sub-categories').show();
    const value = e.target.value;
    if (value === 'example') {
      window.$('.sub-categories .example').slideToggle();
      window.$('.sub-categories .organization').hide();
    } else {
      window.$('.sub-categories .example').hide();
      window.$('.sub-categories .organization').slideToggle();
    }
  }

  handleSubmit(e) {
    e.preventDefault();

    const searchKeyword = this.input.value.trim();
    if (!searchKeyword && !countObj(this.state.query)) {
      return;
    }

    this.props.onSearch(searchKeyword, this.state.query);
    window.$('.sub-categories').hide();
  }
}

// --------------------------------------------------------------------

function parseArticles(optBlock) {
  const chapters = [];
  const chapterArticles = {};

  optBlock.options.forEach((option) => {
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

  return {
    chapters,
    chapterArticles,
  };
}

function countObj(obj) {
  let count = 0;
  for (const key in obj) {
    count += 1;
  }
  return count;
}
