import React from 'react';
import { inQuery } from '../../util/utils';

const ArticleCategory = ({
  query, chapters, articles, onChapterClick, onCheckboxClick
}) => {
  const chapterRows = chapters.map(({ value, name }) => {
    const id = `chapter-${value}`;
    return (
      <li key={id} className="box"><div className="radio-button">
        <input type="radio" name="chapter" id={id}
          onClick={() => onChapterClick(value)}
        />
        <label htmlFor={id}>{name}</label>
      </div></li>
    );
  });

  const articleRows = articles.map(({ value, name }) => {
    const id = `article-${value}`;
    const field = 'a11';
    const checked = inQuery(query, field, value);

    return (
      <li key={id} className="checkbox-wrap">
        <input type="checkbox" name="article"
          id={id} checked={checked}
          onChange={() => onCheckboxClick(field, value)}
        />
        <label className="checkbox" htmlFor={id}>
          <i className="unchecked fa fa-square-o"></i>
          <i className="checked fa fa-check-square"></i>
        </label>
        {' '}
        <label className="label" htmlFor={id}>{name}</label>
      </li>
    );
  });
  return (
    <div className="example">
      <div className="chapters">
        <ul className="shadow">
          {chapterRows}
        </ul>
      </div>
      <div className="articles">
        <ul className="shadow">
          {articleRows}
        </ul>
      </div>
    </div>
  );
};

export default ArticleCategory;



